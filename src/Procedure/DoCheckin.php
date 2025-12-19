<?php

namespace DailyCheckinBundle\Procedure;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Event\AfterCheckinEvent;
use DailyCheckinBundle\Param\DoCheckinParam;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;

#[MethodTag(name: '签到模块')]
#[MethodDoc(summary: '签到')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose(method: 'DoCheckin')]
#[WithMonologChannel(channel: 'procedure')]
class DoCheckin extends LockableProcedure implements LogFormatProcedure
{
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

    /**
     * @phpstan-param DoCheckinParam $param
     */
    public function execute(DoCheckinParam|RpcParamInterface $param): ArrayResult
    {
        $now = CarbonImmutable::now();
        $activity = $this->getActiveActivity($now, $param);
        $checkinDate = $this->getCheckinDate($now, $param);

        // 检查今日是否已签到
        $existingRecord = $this->checkExistingCheckin($activity, $checkinDate);
        if (null !== $existingRecord) {
            return $this->createAlreadyCheckedInResponse($existingRecord);
        }

        // 创建新的签到记录
        $record = $this->createCheckinRecord($activity, $checkinDate);

        // 计算签到次数
        $this->calculateCheckinTimes($record, $activity, $checkinDate);

        // 保存签到记录
        $this->saveCheckinRecord($record);

        // 处理奖励
        $result = $this->processPrizes($activity, $record);

        // 触发事件
        $finalResult = $this->dispatchAfterCheckinEvent($record, $result);
        return new ArrayResult($finalResult);
    }

    private function getActiveActivity(CarbonImmutable $now, DoCheckinParam $param): Activity
    {
        $activity = $this->activityRepository->createQueryBuilder('a')
            ->where('a.id=:id AND a.startTime <= :startTime and a.endTime > :endTime')
            ->setParameter('id', $param->activityId)
            ->setParameter('endTime', $now)
            ->setParameter('startTime', $now)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$activity instanceof Activity) {
            throw new ApiException('暂无活动');
        }

        return $activity;
    }

    private function getCheckinDate(CarbonImmutable $now, DoCheckinParam $param): CarbonImmutable
    {
        return '' !== $param->checkinDate ? CarbonImmutable::parse($param->checkinDate) : $now;
    }

    private function checkExistingCheckin(Activity $activity, CarbonImmutable $checkinDate): ?Record
    {
        $record = $this->recordRepository->findOneBy([
            'user' => $this->security->getUser(),
            'activity' => $activity,
            'checkinDate' => $checkinDate,
        ]);

        return $record instanceof Record ? $record : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function createAlreadyCheckedInResponse(Record $record): array
    {
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

    private function createCheckinRecord(Activity $activity, CarbonImmutable $checkinDate): Record
    {
        $record = new Record();
        $record->setUser($this->security->getUser());
        $record->setActivity($activity);
        $record->setCheckinDate($checkinDate);
        $record->setHasAward(false);

        return $record;
    }

    private function calculateCheckinTimes(Record $record, Activity $activity, CarbonImmutable $checkinDate): void
    {
        $lastRecord = $this->getLastCheckinRecord($activity);

        if (null === $lastRecord || $lastRecord->getCheckinTimes() >= $activity->getTimes()) {
            $record->setCheckinTimes(1);

            return;
        }

        $times = $this->calculateTimesBasedOnCheckinType($activity, $lastRecord, $checkinDate);
        $record->setCheckinTimes($times);
    }

    private function getLastCheckinRecord(Activity $activity): ?Record
    {
        $record = $this->recordRepository
            ->createQueryBuilder('a')
            ->where('a.activity = :activity and a.user = :user')
            ->setParameter('activity', $activity)
            ->setParameter('user', $this->security->getUser())
            ->setMaxResults(1)
            ->orderBy('a.checkinDate', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $record instanceof Record ? $record : null;
    }

    private function calculateTimesBasedOnCheckinType(Activity $activity, Record $lastRecord, CarbonImmutable $checkinDate): int
    {
        if (CheckinType::CONTINUE === $activity->getCheckinType()) {
            return $this->calculateContinuousTimes($lastRecord, $checkinDate);
        }

        if (CheckinType::ACCRUED === $activity->getCheckinType()) {
            $times = $lastRecord->getCheckinTimes();

            return null !== $times ? $times + 1 : 1;
        }

        return 1;
    }

    private function calculateContinuousTimes(Record $lastRecord, CarbonImmutable $checkinDate): int
    {
        $lastCheckinDate = $lastRecord->getCheckinDate();
        if (null !== $lastCheckinDate && $checkinDate->subDay()->format('Ymd') === $lastCheckinDate->format('Ymd')) {
            $times = $lastRecord->getCheckinTimes();

            return null !== $times ? $times + 1 : 1;
        }

        return 1; // 从第一天重新开始
    }

    private function saveCheckinRecord(Record $record): void
    {
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
    }

    /**
     * @return array<string, mixed>
     */
    private function processPrizes(Activity $activity, Record $record): array
    {
        $result = ['hasAward' => false];

        $checkinTimes = $record->getCheckinTimes();
        /** @var array<string, mixed> $prizeRes */
        $prizeRes = $this->checkinPrizeService->getPrize($activity, (int) $checkinTimes);
        $this->logger->info('签到应得奖励', [
            'prizeRes' => $prizeRes,
        ]);

        $result = array_merge($result, $prizeRes);

        if (isset($result['andPrizes']) && is_array($result['andPrizes'])) {
            foreach ($result['andPrizes'] as $andPrize) {
                if ($andPrize instanceof Reward) {
                    $this->checkinPrizeService->sendPrize($andPrize, $record);
                }
            }
        }

        $record->setHasAward((bool) $result['hasAward']);
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        $result['record'] = $record->retrieveApiArray();
        $hasAward = $record->hasAward();
        $result['choseReward'] = (bool) $hasAward && $record->getAwards()->isEmpty();

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function dispatchAfterCheckinEvent(Record $record, array $result): array
    {
        $event = new AfterCheckinEvent();
        $event->setRecord($record);
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        return new ArrayResult($event->getResult());
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '打卡签到';
    }
}
