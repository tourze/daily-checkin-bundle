<?php

namespace DailyCheckinBundle\Procedure;

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

    public function execute(): array
    {
        $activity = $this->activityRepository->findOneBy([
            'id' => $this->activityId,
        ]);
        if (empty($activity)) {
            throw new ApiException('暂无活动');
        }

        $records = $this->recordRepository->findBy([
            'activity' => $activity,
            'user' => $this->security->getUser(),
        ], ['id' => 'DESC']);

        $list = [];
        foreach ($records as $record) {
            $tmp = $record->retrieveApiArray();
            if ($record->hasAward() && $record->getAwards()->isEmpty()) {
                $res = $this->checkinPrizeService->getPrize($record->getActivity(), $record->getCheckinTimes());
                $tmp['orPrizes'] = $res['orPrizes'];
            }
            $tmp['choseReward'] = $record->hasAward() && $record->getAwards()->isEmpty();
            $list[] = $tmp;
        }

        return $list;
    }
}
