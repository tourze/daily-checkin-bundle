<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc(summary: '获取最新的签到记录')]
#[MethodTag(name: '签到模块')]
#[MethodExpose(method: 'GetRecentlyCheckinRecords')]
class GetRecentlyCheckinRecords extends BaseProcedure
{
    #[MethodParam(description: '签到活动ID')]
    public string $activityId;

    #[MethodParam(description: '记录条数')]
    public int $nums = 4;

    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
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
        ], ['id' => 'DESC'], $this->nums);

        $list = [];
        foreach ($records as $record) {
            $list[] = $record->retrieveApiArray();
        }

        return $list;
    }
}
