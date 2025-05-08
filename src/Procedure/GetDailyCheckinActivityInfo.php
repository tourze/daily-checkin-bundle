<?php

namespace DailyCheckinBundle\Procedure;

use Carbon\Carbon;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Event\BeforeReturnCheckinActivityEvent;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc('获取签到活动的内容跟打卡记录')]
#[MethodTag('签到模块')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('GetDailyCheckinActivityInfo')]
class GetDailyCheckinActivityInfo extends BaseProcedure
{
    #[MethodParam('签到活动ID')]
    public string $activityId;

    public function __construct(
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function execute(): array
    {
        $result = [];
        $activity = $this->activityRepository->findOneBy([
            'id' => $this->activityId,
        ]);
        if (empty($activity)) {
            throw new ApiException('暂无活动');
        }

        $result['activity'] = $activity->retrieveApiArray();

        // 签到情况
        $result['accumulatedDays'] = 0;
        $result['todayHadCheckin'] = false;

        $today = Carbon::now()->startOfDay();
        // 累计签到
        if (CheckinType::ACCRUED === $activity->getCheckinType()) {
            /** @var Record[] $records */
            $records = $this->recordRepository->createQueryBuilder('c')
                ->where('c.user = :user and c.activity = :activity')
                ->setParameter('user', $this->security->getUser())
                ->setParameter('activity', $activity)
                ->orderBy('c.id', Criteria::DESC)
                ->getQuery()
                ->getResult();

            $result['record'] = [];
            if (!empty($records)) {
                $times = $activity->getTimes();
                $maxCheckinTimes = $records[0]->getCheckinTimes();
                for ($i = 1; $i <= $times; ++$i) {
                    $key = $maxCheckinTimes - $i;
                    if ($key < 0) {
                        $result['record'][$i] = [];
                    } else {
                        $result['record'][$i] = $records[$key]->getCheckinDate();
                        ++$result['accumulatedDays'];
                        if ($today->equalTo($records[$key]->getCheckinDate())) {
                            $result['todayHadCheckin'] = true;
                        }
                    }
                }
            }

            $result['record'] = array_values($result['record']);
        }

        // 连续签到
        if (CheckinType::CONTINUE === $activity->getCheckinType()) {
            $records = $this->recordRepository->createQueryBuilder('c')
                ->where('c.user = :user AND c.activity = :activity')
                ->setParameter('user', $this->security->getUser())
                ->setParameter('activity', $activity)
                ->setMaxResults($activity->getTimes())
                ->orderBy('c.id', Criteria::DESC)
                ->getQuery()
                ->toIterable();

            $result['dayRecords'] = [];
            foreach ($records as $record) {
                /* @var Record $record */
                $result['dayRecords'][$record->getCheckinDate()->format('Y-m-d')] = $record->retrieveApiArray();
                if ($today->equalTo($record->getCheckinDate())) {
                    $result['todayHadCheckin'] = true;
                }
            }

            // 计算连续签到天数
            $date = Carbon::now();
            while (isset($result['dayRecords'][$date->format('Y-m-d')])) {
                ++$result['accumulatedDays'];
                $date = $date->subDay();
            }

            // 如果今天没签，但是昨天签了，那么数据可能不对
            if (0 === $result['accumulatedDays']) {
                $date = Carbon::yesterday();
                while (isset($result['dayRecords'][$date->format('Y-m-d')])) {
                    ++$result['accumulatedDays'];
                    $date = $date->subDay();
                }
            }
        }

        $event = new BeforeReturnCheckinActivityEvent();
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        return $event->getResult();
    }
}
