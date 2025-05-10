<?php

namespace DailyCheckinBundle\Service;

use Carbon\Carbon;
use CouponBundle\Service\CouponService;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use CreditBundle\Service\TransactionService;
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
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckinPrizeService
{
    public function __construct(
        private readonly RewardRepository $rewardRepository,
        private readonly AwardRepository $awardRepository,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?CouponService $couponService,
        private readonly ?CurrencyService $currencyService,
        private readonly ?AccountService $accountService,
        private readonly ?TransactionService $transactionService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getPrize(Activity $activity, int $times): array
    {
        $now = Carbon::now();
        $rewards = $this->rewardRepository->findBy([
            'activity' => $activity,
            'times' => $times,
        ]);
        if (empty($rewards)) {
            return [
                'hasAward' => false,
                'orPrizes' => [],
                'andPrizes' => [],
            ];
        }

        $andPrizes = [];
        $orPrizes = [];
        foreach ($rewards as $reward) {
            if ($reward->getQuantity() < 1) {
                continue;
            }
            // 判断是否达到每日限制且不是兜底奖项
            if ($reward->getDayLimit() > 0 && !$reward->getIsDefault()) {
                $count = $this->awardRepository->createQueryBuilder('a')
                    ->select('count(a.id)')
                    ->where('a.reward = :reward and a.createTime between :start and :end')
                    ->setParameter('start', $now->clone()->startOfDay())
                    ->setParameter('end', $now->clone()->endOfDay())
                    ->setParameter('reward', $reward)
                    ->getQuery()
                    ->getSingleScalarResult();
                if ($count >= $reward->getDayLimit()) {
                    $this->logger->info("{$reward->getId()} 奖品已达到每日限制数量", [
                        'dayLimit' => $reward->getDayLimit(),
                        'hadCount' => $count,
                    ]);
                    continue;
                }
            }

            if (RewardGetType::AND == $reward->getRewardGetType()) {
                $andPrizes[] = $reward;
            } else {
                $orPrizes[] = $reward->retrieveApiArray();
            }
        }

        $this->logger->info('打印orPrizes', [
            'orPrizes' => $orPrizes,
        ]);
        if (!empty($orPrizes)) {
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

        if (empty($andPrizes)) {
            $reward = $this->rewardRepository->findOneBy([
                'activity' => $activity,
                'times' => $times,
                'isDefault' => true,
            ]);
            $andPrizes[] = $reward;
        }

        return [
            'hasAward' => true,
            'orPrizes' => [],
            'andPrizes' => $andPrizes,
        ];
    }

    public function sendPrize(Reward $reward, Record $record): void
    {
        if (RewardType::COUPON === $reward->getType() && $this->couponService) {
            // 发送优惠券
            $coupon = $this->couponService->detectCoupon($reward->getValue());
            $this->couponService->sendCode($record->getUser(), $coupon);

            $award = new Award();
            $award->setRecord($record);
            $award->setUser($record->getUser());
            $award->setReward($reward);
            $this->entityManager->persist($award);
            $record->addAward($award);
            $this->entityManager->persist($record);
            $this->entityManager->flush();
        }

        if (RewardType::CREDIT === $reward->getType() && $this->accountService && $this->transactionService) {
            // 给积分，point取奖项里的值
            $integralName = $_ENV['DEFAULT_CREDIT_CURRENCY_CODE'] ?? 'CREDIT';
            $currency = $this->currencyService->getCurrencyByCode($integralName);
            $inAccount = $this->accountService->getAccountByUser($record->getUser(), $currency);

            $remark = $_ENV["CHECKIN_AWARD_CREDIT_{$record->getCheckinTimes()}_REMARK"]
                ?? $_ENV['CHECKIN_AWARD_CREDIT_REMARK']
                ?? "签到第{$record->getCheckinTimes()}次奖励";
            $this->transactionService->increase(
                'DC-' . $record->getId(),
                $inAccount,
                intval($reward->getValue()),
                $remark,
            );

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
}
