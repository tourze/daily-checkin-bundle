<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\SubmitCheckinAward;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Repository\RewardRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SubmitCheckinAwardTest extends TestCase
{
    private SubmitCheckinAward $procedure;

    protected function setUp(): void
    {
        $recordRepository = $this->createMock(RecordRepository::class);
        $rewardRepository = $this->createMock(RewardRepository::class);
        $checkinPrizeService = $this->createMock(CheckinPrizeService::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->procedure = new SubmitCheckinAward(
            $recordRepository,
            $rewardRepository,
            $checkinPrizeService,
            $logger
        );
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(SubmitCheckinAward::class, $this->procedure);
    }
}