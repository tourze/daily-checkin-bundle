<?php

namespace DailyCheckinBundle\Tests\Event;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Event\AfterCheckinEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AfterCheckinEvent::class)]
final class AfterCheckinEventTest extends AbstractEventTestCase
{
    private AfterCheckinEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new AfterCheckinEvent();
    }

    public function testGetSetResult(): void
    {
        $result = ['status' => 'success', 'message' => 'Checkin completed'];

        $this->event->setResult($result);
        $this->assertSame($result, $this->event->getResult());
    }

    public function testGetSetRecord(): void
    {
        $record = new Record();
        $record->setCheckinDate(new \DateTime());

        $this->event->setRecord($record);
        $this->assertSame($record, $this->event->getRecord());
    }

    public function testGetSetAward(): void
    {
        $award = new Award();
        $this->event->setAward($award);
        $this->assertSame($award, $this->event->getAward());
    }

    public function testAwardCanBeNull(): void
    {
        $this->assertNull($this->event->getAward());

        $this->event->setAward(null);
        $this->assertNull($this->event->getAward());
    }

    public function testInitialResultIsEmptyArray(): void
    {
        $this->assertSame([], $this->event->getResult());
    }
}
