<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\DoCheckin;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoCheckinTest extends TestCase
{
    private DoCheckin $procedure;
    private ActivityRepository $activityRepository;
    private RecordRepository $recordRepository;
    private CheckinPrizeService $checkinPrizeService;
    private EntityManagerInterface $entityManager;
    private Security $security;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->activityRepository = $this->createMock(ActivityRepository::class);
        $this->recordRepository = $this->createMock(RecordRepository::class);
        $this->checkinPrizeService = $this->createMock(CheckinPrizeService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->procedure = new DoCheckin(
            $this->activityRepository,
            $this->recordRepository,
            $this->checkinPrizeService,
            $this->security,
            $this->logger,
            $this->eventDispatcher,
            $this->entityManager
        );
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(DoCheckin::class, $this->procedure);
    }

    public function testImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf('Tourze\JsonRPCLockBundle\Procedure\LockableProcedure', $this->procedure);
        $this->assertInstanceOf('Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure', $this->procedure);
    }
}