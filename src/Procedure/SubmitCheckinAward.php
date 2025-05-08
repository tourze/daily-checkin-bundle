<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Repository\RewardRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag('签到模块')]
#[MethodDoc('提交签到奖励')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose('SubmitCheckinAward')]
class SubmitCheckinAward extends LockableProcedure
{
    /**
     * @var string 奖励ID
     */
    public string $rewardId = '';

    /**
     * @var string 签到记录ID
     */
    public string $recordId = '';

    public function __construct(
        private readonly RecordRepository $recordRepository,
        private readonly RewardRepository $rewardRepository,
        private readonly CheckinPrizeService $checkinPrizeService,
        private readonly LoggerInterface $procedureLogger,
    ) {
    }

    public function execute(): array
    {
        $record = $this->recordRepository->find($this->recordId);
        if (empty($record)) {
            throw new ApiException('签到记录不存在');
        }
        if (!$record->hasAward()) {
            $this->procedureLogger->error('签到记录未获得奖品', [
                'record' => $record->retrieveApiArray(),
            ]);
            throw new ApiException('无法获得奖品');
        }

        if (!$record->getAwards()->isEmpty()) {
            $this->procedureLogger->error('签到记录已获得过奖品', [
                'record' => $record->retrieveApiArray(),
            ]);
            throw new ApiException('已获得奖品');
        }

        $reward = $this->rewardRepository->find($this->rewardId);
        if (empty($reward)) {
            throw new ApiException('奖励不存在');
        }

        $getReward = $this->checkinPrizeService->getPrize($record->getActivity(), $record->getCheckinTimes());
        $rewardIds = array_column($getReward['orPrizes'], 'id');
        if (!in_array($this->rewardId, $rewardIds)) {
            throw new ApiException('无法获得该奖品');
        }

        $this->checkinPrizeService->sendPrize($reward, $record);

        return $record->retrieveApiArray();
    }
}
