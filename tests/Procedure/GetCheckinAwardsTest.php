<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\GetCheckinAwards;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GetCheckinAwardsTest extends TestCase
{
    private GetCheckinAwards $procedure;

    protected function setUp(): void
    {
        $security = $this->createMock(Security::class);
        $activityRepository = $this->createMock(ActivityRepository::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->procedure = new GetCheckinAwards(
            $security,
            $activityRepository,
            $recordRepository,
            $eventDispatcher
        );
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetCheckinAwards::class, $this->procedure);
    }
}