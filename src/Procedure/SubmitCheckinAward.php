<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Param\SubmitCheckinAwardParam;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Repository\RewardRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '签到模块')]
#[MethodDoc(summary: '提交签到奖励')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose(method: 'SubmitCheckinAward')]
#[WithMonologChannel(channel: 'procedure')]
class SubmitCheckinAward extends LockableProcedure
{
    public function __construct(
        private readonly RecordRepository $recordRepository,
        private readonly RewardRepository $rewardRepository,
        private readonly CheckinPrizeService $checkinPrizeService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @phpstan-param SubmitCheckinAwardParam $param
     */
    public function execute(SubmitCheckinAwardParam|RpcParamInterface $param): ArrayResult
    {
        $record = $this->recordRepository->find($param->recordId);
        if (!$record instanceof Record) {
            throw new ApiException('签到记录不存在');
        }
        if (true !== $record->hasAward()) {
            $this->logger->error('签到记录未获得奖品', [
                'record' => $record->retrieveApiArray(),
            ]);
            throw new ApiException('无法获得奖品');
        }

        if (!$record->getAwards()->isEmpty()) {
            $this->logger->error('签到记录已获得过奖品', [
                'record' => $record->retrieveApiArray(),
            ]);
            throw new ApiException('已获得奖品');
        }

        $reward = $this->rewardRepository->find($param->rewardId);
        if (!$reward instanceof Reward) {
            throw new ApiException('奖励不存在');
        }

        $activity = $record->getActivity();
        $checkinTimes = $record->getCheckinTimes();
        if (null === $activity || null === $checkinTimes) {
            throw new ApiException('签到信息不完整');
        }
        $getReward = $this->checkinPrizeService->getPrize($activity, $checkinTimes);
        $orPrizes = $getReward['orPrizes'] ?? [];
        if (!is_array($orPrizes)) {
            throw new ApiException('奖品数据格式错误');
        }
        $rewardIds = array_column($orPrizes, 'id');
        if (!in_array($param->rewardId, $rewardIds, true)) {
            throw new ApiException('无法获得该奖品');
        }

        $this->checkinPrizeService->sendPrize($reward, $record);

        return $record->retrieveApiArray();
    }
}
