<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc(summary: '获取签到活动的奖励')]
#[MethodTag(name: '签到模块')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetUserCheckinRecords')]
class GetUserCheckinRecords extends BaseProcedure
{
    #[MethodParam(description: '签到活动ID')]
    public string $activityId;

    public function __construct(
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
        private readonly CheckinPrizeService $checkinPrizeService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $activity = $this->getActivity();
        $records = $this->getRecords($activity);
        $list = $this->processRecords($records);

        return [
            'data' => $list,
        ];
    }

    private function getActivity(): Activity
    {
        $activity = $this->activityRepository->findOneBy([
            'id' => $this->activityId,
        ]);

        if (!$activity instanceof Activity) {
            throw new ApiException('暂无活动');
        }

        return $activity;
    }

    /**
     * @return array<Record>
     */
    private function getRecords(Activity $activity): array
    {
        return $this->recordRepository->findByActivityAndUserWithJoins(
            $activity,
            $this->security->getUser()
        );
    }

    /**
     * @param array<Record> $records
     * @return array<array<string, mixed>>
     */
    private function processRecords(array $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $recordData = $this->processRecord($record);
            $list[] = $recordData;
        }

        return $list;
    }

    /**
     * @return array<string, mixed>
     */
    private function processRecord(Record $record): array
    {
        $tmp = $record->retrieveApiArray();

        if ($this->shouldAddOrPrizes($record)) {
            $orPrizes = $this->getOrPrizes($record);
            if (null !== $orPrizes) {
                $tmp['orPrizes'] = $orPrizes;
            }
        }

        $tmp['choseReward'] = $this->shouldChooseReward($record);

        return $tmp;
    }

    private function shouldAddOrPrizes(Record $record): bool
    {
        $hasAward = $record->hasAward();

        return (true === $hasAward) && $record->getAwards()->isEmpty();
    }

    private function shouldChooseReward(Record $record): bool
    {
        $hasAward = $record->hasAward();

        return (true === $hasAward) && $record->getAwards()->isEmpty();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getOrPrizes(Record $record): ?array
    {
        $activity = $record->getActivity();
        $checkinTimes = $record->getCheckinTimes();

        if (null === $activity || null === $checkinTimes) {
            return null;
        }

        $res = $this->checkinPrizeService->getPrize($activity, $checkinTimes);

        $orPrizes = $res['orPrizes'] ?? null;

        // 确保返回类型符合声明：array<string, mixed>|null
        if (is_array($orPrizes)) {
            /** @var array<string, mixed> $orPrizes */
            return $orPrizes;
        }

        return null;
    }
}
