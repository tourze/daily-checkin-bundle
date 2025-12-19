<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Param;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class GetRecentlyCheckinRecordsParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '签到活动ID')]
        public string $activityId,
        #[MethodParam(description: '记录条数')]
        public int $nums = 4,
    ) {
    }
}
