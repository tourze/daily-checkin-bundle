<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\GetDailyCheckinActivityInfo;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GetDailyCheckinActivityInfoTest extends TestCase
{
    private GetDailyCheckinActivityInfo $procedure;

    protected function setUp(): void
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $security = $this->createMock(Security::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->procedure = new GetDailyCheckinActivityInfo(
            $security,
            $activityRepository,
            $recordRepository,
            $eventDispatcher
        );
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetDailyCheckinActivityInfo::class, $this->procedure);
    }
}