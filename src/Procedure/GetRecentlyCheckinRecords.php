<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Param\GetRecentlyCheckinRecordsParam;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc(summary: '获取最新的签到记录')]
#[MethodTag(name: '签到模块')]
#[MethodExpose(method: 'GetRecentlyCheckinRecords')]
class GetRecentlyCheckinRecords extends BaseProcedure
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
    ) {
    }

    /**
     * @phpstan-param GetRecentlyCheckinRecordsParam $param
     */
    public function execute(GetRecentlyCheckinRecordsParam|RpcParamInterface $param): ArrayResult
    {
        $activity = $this->activityRepository->findOneBy([
            'id' => $param->activityId,
        ]);
        if (!$activity instanceof Activity) {
            throw new ApiException('暂无活动');
        }

        $records = $this->recordRepository->findRecentRecordsWithJoins($activity, $param->nums);

        /** @var array<Record> $records */
        $list = [];
        foreach ($records as $record) {
            $list[] = $record->retrieveApiArray();
        }

        return new ArrayResult(['data' => $list]);
    }
}
