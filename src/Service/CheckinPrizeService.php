<?php

namespace DailyCheckinBundle\Service;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Event\BeforeOrPrizeReturnEvent;
use DailyCheckinBundle\Repository\AwardRepository;
use DailyCheckinBundle\Repository\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\CreditServiceContracts\Service\CreditAccountServiceInterface;
use Tourze\CreditServiceContracts\Service\CreditTransactionServiceInterface;
use Tourze\CreditServiceContracts\Service\CreditTypeServiceInterface;

#[WithMonologChannel(channel: 'daily_checkin')]
readonly class CheckinPrizeService
{
    public function __construct(
        private RewardRepository $rewardRepository,
        private AwardRepository $awardRepository,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
        private ?CouponService $couponService,
        private ?CreditTypeServiceInterface $creditTypeService,
        private ?CreditAccountServiceInterface $accountService,
        private ?CreditTransactionServiceInterface $transactionService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 获取奖品信息
     * 不考虑并发：奖品获取逻辑允许并发执行，重复获取由业务层控制
     */
    /**
     * @return array<string, mixed>
     */
    public function getPrize(Activity $activity, int $times): array
    {
        $rewards = $this->rewardRepository->findBy([
            'activity' => $activity,
            'times' => $times,
        ]);

        if ([] === $rewards) {
            return $this->createEmptyPrizeResponse();
        }

        $availableRewards = $this->filterAvailableRewards($rewards);
        $andPrizes = [];
        $orPrizes = [];

        foreach ($availableRewards as $reward) {
            $rewardGetType = $reward->getRewardGetType();
            if (RewardGetType::AND === $rewardGetType) {
                $andPrizes[] = $reward;
            } else {
                $orPrizes[] = $reward->retrieveApiArray();
            }
        }

        return $this->buildPrizeResponse($orPrizes, $andPrizes, $activity, $times);
    }

    /**
     * 创建空的奖品响应
     * 不考虑并发：返回静态数据结构
     */
    /**
     * @return array<string, mixed>
     */
    private function createEmptyPrizeResponse(): array
    {
        return [
            'hasAward' => false,
            'orPrizes' => [],
            'andPrizes' => [],
        ];
    }

    /**
     * @param array<Reward> $rewards
     * @return array<Reward>
     */
    private function filterAvailableRewards(array $rewards): array
    {
        $availableRewards = [];

        foreach ($rewards as $reward) {
            if ($this->isRewardAvailable($reward)) {
                $availableRewards[] = $reward;
            }
        }

        return $availableRewards;
    }

    private function isRewardAvailable(Reward $reward): bool
    {
        if ($reward->getQuantity() < 1) {
            return false;
        }

        return $this->checkDailyLimit($reward);
    }

    private function checkDailyLimit(Reward $reward): bool
    {
        // 判断是否达到每日限制且不是兜底奖项
        if ($reward->getDayLimit() <= 0 || true === $reward->getIsDefault()) {
            return true;
        }

        $now = CarbonImmutable::now();
        $count = $this->awardRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.reward = :reward and a.createTime between :start and :end')
            ->setParameter('start', $now->startOfDay())
            ->setParameter('end', $now->endOfDay())
            ->setParameter('reward', $reward)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($count >= $reward->getDayLimit()) {
            $this->logger->info("{$reward->getId()} 奖品已达到每日限制数量", [
                'dayLimit' => $reward->getDayLimit(),
                'hadCount' => $count,
            ]);

            return false;
        }

        return true;
    }

    /**
     * 构建奖品响应数据
     * 不考虑并发：响应数据组装操作不涉及并发问题
     */
    /**
     * @param array<mixed> $orPrizes
     * @param array<Reward> $andPrizes
     * @return array<string, mixed>
     */
    private function buildPrizeResponse(array $orPrizes, array $andPrizes, Activity $activity, int $times): array
    {
        $this->logger->info('打印orPrizes', [
            'orPrizes' => $orPrizes,
        ]);

        if ([] !== $orPrizes) {
            return $this->processOrPrizes($orPrizes, $andPrizes);
        }

        if ([] === $andPrizes) {
            $andPrizes = $this->getDefaultReward($activity, $times);
        }

        return [
            'hasAward' => true,
            'orPrizes' => [],
            'andPrizes' => $andPrizes,
        ];
    }

    /**
     * 处理Or类型奖品
     * 不考虑并发：事件派发操作不涉及并发冲突
     */
    /**
     * @param array<mixed> $orPrizes
     * @param array<Reward> $andPrizes
     * @return array<string, mixed>
     */
    private function processOrPrizes(array $orPrizes, array $andPrizes): array
    {
        $event = new BeforeOrPrizeReturnEvent();
        $event->setOrPrizes($orPrizes);
        $event->setAndPrizes($andPrizes);
        $this->eventDispatcher->dispatch($event);
        $orPrizes = $event->getOrPrizes();

        return [
            'hasAward' => true, // 表示有奖品
            'orPrizes' => $orPrizes,
            'andPrizes' => [],
        ];
    }

    /**
     * @return array<Reward>
     */
    private function getDefaultReward(Activity $activity, int $times): array
    {
        $reward = $this->rewardRepository->findOneBy([
            'activity' => $activity,
            'times' => $times,
            'isDefault' => true,
        ]);

        return null !== $reward ? [$reward] : [];
    }

    /**
     * 发送奖品给用户
     * 不考虑并发：奖品发送由签到流程控制，不会出现重复发送
     */
    public function sendPrize(Reward $reward, Record $record): void
    {
        if (RewardType::COUPON === $reward->getType()) {
            $this->sendCouponPrize($reward, $record);
        }

        if (RewardType::CREDIT === $reward->getType()) {
            $this->sendCreditPrize($reward, $record);
        }
    }

    private function sendCouponPrize(Reward $reward, Record $record): void
    {
        if (null === $this->couponService) {
            return;
        }

        $value = $reward->getValue();
        $user = $record->getUser();
        if (null !== $value && null !== $user) {
            $coupon = $this->couponService->detectCoupon($value);
            $this->couponService->sendCode($user, $coupon);
        }

        $this->createAward($reward, $record);
    }

    private function sendCreditPrize(Reward $reward, Record $record): void
    {
        if (null === $this->accountService || null === $this->transactionService || null === $this->creditTypeService) {
            return;
        }

        $user = $record->getUser();
        if (null === $user) {
            return;
        }

        $integralName = $_ENV['DEFAULT_CREDIT_CURRENCY_CODE'] ?? 'CREDIT';
        if (!is_string($integralName)) {
            $integralName = 'CREDIT';
        }
        $creditType = $this->creditTypeService->getCreditTypeByCode($integralName);

        if (null !== $creditType) {
            $inAccount = $this->accountService->getOrCreateAccount($user, $creditType->getId());

            $remark = $_ENV["CHECKIN_AWARD_CREDIT_{$record->getCheckinTimes()}_REMARK"]
                ?? $_ENV['CHECKIN_AWARD_CREDIT_REMARK']
                ?? "签到第{$record->getCheckinTimes()}次奖励";

            // TODO: 积分发放功能需要根据实际积分服务实现进行调整
            // 当前CreditTransactionServiceInterface接口中没有直接的积分增加方法
            // 需要调用具体的积分服务实现，如CreditIncreaseService
            // 这部分功能需要后续完善，暂时只创建奖励记录

            $this->logger->info('积分奖励记录已创建，积分发放功能待完善', [
                'record_id' => $record->getId(),
                'reward_id' => $reward->getId(),
                'amount' => $reward->getValue(),
                'user_class' => get_class($user),
            ]);

            $this->createAward($reward, $record);
        }
    }

    private function createAward(Reward $reward, Record $record): void
    {
        $award = new Award();
        $award->setRecord($record);
        $award->setUser($record->getUser());
        $award->setReward($reward);
        $this->entityManager->persist($award);
        $record->addAward($award);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }
}
