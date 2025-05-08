<?php

namespace DailyCheckinBundle\Procedure;

use Carbon\Carbon;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Event\AfterCheckinEvent;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;

#[MethodTag('签到模块')]
#[MethodDoc('签到')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose('DoCheckin')]
#[WithMonologChannel('procedure')]
class DoCheckin extends LockableProcedure implements LogFormatProcedure
{
    #[MethodParam('签到活动ID')]
    public string $activityId;

    /**
     * @var string 签到日期，当不传入时，代表的是请求时日期
     */
    public string $checkinDate = '';

    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
        private readonly CheckinPrizeService $checkinPrizeService,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // todo 暂没考虑连续签到的情况(补签)
    public function execute(): array
    {
        $result = [];
        $now = Carbon::now();
        $activity = $this->activityRepository->createQueryBuilder('a')
            ->where('a.id=:id AND a.startTime <= :startTime and a.endTime > :endTime')
            ->setParameter('id', $this->activityId)
            ->setParameter('endTime', $now)
            ->setParameter('startTime', $now)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if (empty($activity)) {
            throw new ApiException('暂无活动');
        }
        /* @var Activity $activity */

        $this->checkinDate = $this->checkinDate ? Carbon::parse($this->checkinDate) : $now;
        $checkinDate = $this->checkinDate ? Carbon::parse($this->checkinDate) : $now;

        // todo 测试才允许一天签到多次
        $record = $this->recordRepository->findOneBy([
            'user' => $this->security->getUser(),
            'activity' => $activity,
            'checkinDate' => $checkinDate,
        ]);
        if ($record) {
            return [
                'id' => $record->getId(),
                '__showToast' => [
                    'title' => '今日已签到',
                    'icon' => 'fail',
                ],
                'times' => $record->getCheckinTimes(),
                'award' => '',
            ];
        }

        $record = new Record();
        $record->setUser($this->security->getUser());
        $record->setActivity($activity);
        $record->setCheckinDate($checkinDate);
        $record->setHasAward(false);

        // 查最近一次签到记录的签到次数
        /** @var Record|null $lastRecord */
        $lastRecord = $this->recordRepository
            ->createQueryBuilder('a')
            ->where('a.activity = :activity and a.user = :user')
            ->setParameter('activity', $activity)
            ->setParameter('user', $this->security->getUser())
            ->setMaxResults(1)
            ->orderBy('a.checkinDate', Criteria::DESC)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$lastRecord || $lastRecord->getCheckinTimes() >= $activity->getTimes()) {
            $record->setCheckinTimes(1);
        } else {
            // 连续签到
            if (CheckinType::CONTINUE === $activity->getCheckinType()) {
                if ($checkinDate->clone()->subDay()->format('Ymd') === $lastRecord->getCheckinDate()->format('Ymd')) {
                    $times = $lastRecord->getCheckinTimes() + 1;
                    $record->setCheckinTimes($times);
                } else {
                    // 从第一天重新开始
                    $record->setCheckinTimes(1);
                }
            }

            // 累计签到
            if (CheckinType::ACCRUED === $activity->getCheckinType()) {
                $times = $lastRecord->getCheckinTimes() + 1;
                $record->setCheckinTimes($times);
            }
        }

        // 保存签到记录
        try {
            $this->entityManager->persist($record);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('保存签到记录时发生异常', [
                'exception' => $exception,
                'record' => $record,
            ]);
            throw new ApiException('签到时发生未知异常，请稍后重试', previous: $exception);
        }

        $result['hasAward'] = false;

        $prizeRes = $this->checkinPrizeService->getPrize($activity, $record->getCheckinTimes());
        $this->logger->info('签到应得奖励', [
            'prizeRes' => $prizeRes,
        ]);
        $result = array_merge($result, $prizeRes);
        if (!empty($result['andPrizes'])) {
            foreach ($result['andPrizes'] as $andPrize) {
                $this->checkinPrizeService->sendPrize($andPrize, $record);
            }
        }

        $record->setHasAward($result['hasAward']);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
        $result['record'] = $record->retrieveApiArray();
        $result['choseReward'] = $record->hasAward() && $record->getAwards()->isEmpty();

        $event = new AfterCheckinEvent();
        $event->setRecord($record);
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        return $event->getResult();
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '打卡签到';
    }
}
