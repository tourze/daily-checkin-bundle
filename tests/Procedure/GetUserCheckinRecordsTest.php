<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\GetUserCheckinRecords;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use DailyCheckinBundle\Service\CheckinPrizeService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GetUserCheckinRecordsTest extends TestCase
{
    private GetUserCheckinRecords $procedure;

    protected function setUp(): void
    {
        $security = $this->createMock(Security::class);
        $activityRepository = $this->createMock(ActivityRepository::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $checkinPrizeService = $this->createMock(CheckinPrizeService::class);

        $this->procedure = new GetUserCheckinRecords(
            $security,
            $activityRepository,
            $recordRepository,
            $checkinPrizeService
        );
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetUserCheckinRecords::class, $this->procedure);
    }
}