<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\GetRecentlyCheckinRecords;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use PHPUnit\Framework\TestCase;

class GetRecentlyCheckinRecordsTest extends TestCase
{
    private GetRecentlyCheckinRecords $procedure;

    protected function setUp(): void
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $this->procedure = new GetRecentlyCheckinRecords($activityRepository, $recordRepository);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetRecentlyCheckinRecords::class, $this->procedure);
    }
}