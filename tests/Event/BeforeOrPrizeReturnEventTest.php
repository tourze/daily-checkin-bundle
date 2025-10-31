<?php

namespace DailyCheckinBundle\Tests\Event;

use DailyCheckinBundle\Event\BeforeOrPrizeReturnEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(BeforeOrPrizeReturnEvent::class)]
final class BeforeOrPrizeReturnEventTest extends AbstractEventTestCase
{
    private BeforeOrPrizeReturnEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new BeforeOrPrizeReturnEvent();
    }

    public function testGetSetResult(): void
    {
        $result = ['status' => 'success', 'data' => []];

        $this->event->setResult($result);
        $this->assertSame($result, $this->event->getResult());
    }

    public function testGetSetOrPrizes(): void
    {
        $orPrizes = [
            'prize1' => ['id' => 1, 'name' => 'Prize 1'],
            'prize2' => ['id' => 2, 'name' => 'Prize 2'],
        ];

        $this->event->setOrPrizes($orPrizes);
        $this->assertSame($orPrizes, $this->event->getOrPrizes());
    }

    public function testGetSetAndPrizes(): void
    {
        $andPrizes = [
            'prize3' => ['id' => 3, 'name' => 'Prize 3'],
            'prize4' => ['id' => 4, 'name' => 'Prize 4'],
        ];

        $this->event->setAndPrizes($andPrizes);
        $this->assertSame($andPrizes, $this->event->getAndPrizes());
    }

    public function testInitialValuesAreEmptyArrays(): void
    {
        $this->assertSame([], $this->event->getResult());
        $this->assertSame([], $this->event->getOrPrizes());
        $this->assertSame([], $this->event->getAndPrizes());
    }
}
