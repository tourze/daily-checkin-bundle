<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Param;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class SubmitCheckinAwardParam implements RpcParamInterface
{
    public function __construct(
        public string $rewardId = '',
        public string $recordId = '',
    ) {
    }
}
