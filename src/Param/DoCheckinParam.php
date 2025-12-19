<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Param;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class DoCheckinParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '签到活动ID')]
        public string $activityId,
        #[MethodParam(description: '签到日期,当不传入时,代表的是请求时日期')]
        public string $checkinDate = '',
    ) {
    }
}
