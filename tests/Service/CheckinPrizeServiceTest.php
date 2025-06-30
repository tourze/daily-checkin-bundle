<?php

namespace DailyCheckinBundle\Tests\Service;

use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use CreditBundle\Service\TransactionService;
use DailyCheckinBundle\Repository\AwardRepository;
use DailyCheckinBundle\Repository\RewardRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\CouponCoreBundle\Service\CouponService;

class CheckinPrizeServiceTest extends TestCase
{
    private CheckinPrizeService $service;
    private RewardRepository $rewardRepository;
    private AwardRepository $awardRepository;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;
    private CouponService $couponService;
    private TransactionService $transactionService;
    private AccountService $accountService;
    private CurrencyService $currencyService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->rewardRepository = $this->createMock(RewardRepository::class);
        $this->awardRepository = $this->createMock(AwardRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->couponService = $this->createMock(CouponService::class);
        $this->transactionService = $this->createMock(TransactionService::class);
        $this->accountService = $this->createMock(AccountService::class);
        $this->currencyService = $this->createMock(CurrencyService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new CheckinPrizeService(
            $this->rewardRepository,
            $this->awardRepository,
            $this->logger,
            $this->eventDispatcher,
            $this->couponService,
            $this->currencyService,
            $this->accountService,
            $this->transactionService,
            $this->entityManager
        );
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(CheckinPrizeService::class, $this->service);
    }

    public function testServiceWithNullCouponService(): void
    {
        $service = new CheckinPrizeService(
            $this->rewardRepository,
            $this->awardRepository,
            $this->logger,
            $this->eventDispatcher,
            null, // couponService 可以为 null
            $this->currencyService,
            $this->accountService,
            $this->transactionService,
            $this->entityManager
        );

        $this->assertInstanceOf(CheckinPrizeService::class, $service);
    }
}