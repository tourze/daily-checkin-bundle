<?php

namespace DailyCheckinBundle\Procedure;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Event\BeforeReturnCheckinActivityEvent;
use DailyCheckinBundle\Param\GetDailyCheckinActivityInfoParam;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc(summary: '获取签到活动的内容跟打卡记录')]
#[MethodTag(name: '签到模块')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetDailyCheckinActivityInfo')]
class GetDailyCheckinActivityInfo extends BaseProcedure
{
    public function __construct(
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @phpstan-param GetDailyCheckinActivityInfoParam $param
     */
    public function execute(GetDailyCheckinActivityInfoParam|RpcParamInterface $param): ArrayResult
    {
        $activity = $this->getActivity($param);
        $result = [
            'activity' => $activity->retrieveApiArray(),
            'accumulatedDays' => 0,
            'todayHadCheckin' => false,
        ];

        if (CheckinType::ACCRUED === $activity->getCheckinType()) {
            $result = $this->processAccruedCheckin($activity, $result);
        }

        if (CheckinType::CONTINUE === $activity->getCheckinType()) {
            $result = $this->processContinuousCheckin($activity, $result);
        }

        $finalResult = $this->dispatchEvent($result);
        return new ArrayResult($finalResult);
    }

    private function getActivity(GetDailyCheckinActivityInfoParam $param): Activity
    {
        $activity = $this->activityRepository->findOneBy([
            'id' => $param->activityId,
        ]);

        if (!$activity instanceof Activity) {
            throw new ApiException('暂无活动');
        }

        return $activity;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function processAccruedCheckin(Activity $activity, array $result): array
    {
        $records = $this->getAccruedRecords($activity);
        $result['record'] = [];

        if ([] !== $records) {
            $result = $this->buildAccruedRecordData($records, $activity, $result);
        }

        /** @var array<string, mixed> $record */
        $record = $result['record'] ?? [];
        $result['record'] = array_values($record);

        return $result;
    }

    /**
     * @return array<int, Record>
     */
    private function getAccruedRecords(Activity $activity): array
    {
        $result = $this->recordRepository->createQueryBuilder('c')
            ->where('c.user = :user and c.activity = :activity')
            ->setParameter('user', $this->security->getUser())
            ->setParameter('activity', $activity)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));
        /** @var array<int, Record> $result */

        return $result;
    }

    /**
     * @param array<Record> $records
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function buildAccruedRecordData(array $records, Activity $activity, array $result): array
    {
        $today = CarbonImmutable::now()->startOfDay();
        $times = $activity->getTimes();
        $maxCheckinTimes = $this->getMaxCheckinTimes($records);

        for ($i = 1; $i <= $times; ++$i) {
            $key = $maxCheckinTimes - $i;
            $result = $this->processAccruedRecord($records, $key, $i, $today, $result);
        }

        return $result;
    }

    /**
     * @param array<Record> $records
     */
    private function getMaxCheckinTimes(array $records): int
    {
        if ([] === $records) {
            return 0;
        }

        $checkinTimes = $records[0]->getCheckinTimes();

        return $checkinTimes ?? 0;
    }

    /**
     * @param array<Record> $records
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function processAccruedRecord(array $records, int $key, int $i, CarbonImmutable $today, array $result): array
    {
        // 初始化record数组如果不存在
        if (!isset($result['record']) || !is_array($result['record'])) {
            $result['record'] = [];
        }

        /** @var array<int, mixed> $recordArray */
        $recordArray = $result['record'];

        if ($key < 0 || !isset($records[$key])) {
            $recordArray[$i] = [];
            $result['record'] = $recordArray;

            return $result;
        }

        $record = $records[$key];
        $recordArray[$i] = $record->getCheckinDate();
        $result['record'] = $recordArray;

        // 安全地处理 accumulatedDays
        $accumulatedDays = $result['accumulatedDays'] ?? 0;
        if (is_int($accumulatedDays)) {
            $result['accumulatedDays'] = $accumulatedDays + 1;
        }

        $checkinDate = $record->getCheckinDate();
        if ($this->isCheckinToday($checkinDate, $today)) {
            $result['todayHadCheckin'] = true;
        }

        return $result;
    }

    private function isCheckinToday(?\DateTimeInterface $checkinDate, CarbonImmutable $today): bool
    {
        if (null === $checkinDate) {
            return false;
        }

        $recordDate = CarbonImmutable::instance($checkinDate)->startOfDay();

        return $today->equalTo($recordDate);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function processContinuousCheckin(Activity $activity, array $result): array
    {
        $records = $this->getContinuousRecords($activity);
        $result['dayRecords'] = [];
        $today = CarbonImmutable::now()->startOfDay();

        foreach ($records as $record) {
            assert($record instanceof Record);
            $checkinDate = $record->getCheckinDate();
            if (null !== $checkinDate) {
                $result['dayRecords'][$checkinDate->format('Y-m-d')] = $record->retrieveApiArray();
                $recordDate = CarbonImmutable::instance($checkinDate)->startOfDay();
                if ($today->equalTo($recordDate)) {
                    $result['todayHadCheckin'] = true;
                }
            }
        }

        $result['accumulatedDays'] = $this->calculateContinuousDays($result['dayRecords']);

        return $result;
    }

    /**
     * @return iterable<Record>
     */
    private function getContinuousRecords(Activity $activity): iterable
    {
        $result = $this->recordRepository->createQueryBuilder('c')
            ->where('c.user = :user AND c.activity = :activity')
            ->setParameter('user', $this->security->getUser())
            ->setParameter('activity', $activity)
            ->setMaxResults($activity->getTimes())
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->toIterable()
        ;

        /** @var iterable<Record> $result */
        return $result;
    }

    /**
     * @param array<string, mixed> $dayRecords
     */
    private function calculateContinuousDays(array $dayRecords): int
    {
        $accumulatedDays = 0;

        // 从今天开始计算连续天数
        $date = CarbonImmutable::now();
        while (isset($dayRecords[$date->format('Y-m-d')])) {
            ++$accumulatedDays;
            $date = $date->subDay();
        }

        // 如果今天没签但昨天签了，从昨天开始计算
        if (0 === $accumulatedDays) {
            $date = CarbonImmutable::yesterday();
            while (isset($dayRecords[$date->format('Y-m-d')])) {
                ++$accumulatedDays;
                $date = $date->subDay();
            }
        }

        return $accumulatedDays;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function dispatchEvent(array $result): array
    {
        $event = new BeforeReturnCheckinActivityEvent();
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        return $event->getResult();
    }
}
