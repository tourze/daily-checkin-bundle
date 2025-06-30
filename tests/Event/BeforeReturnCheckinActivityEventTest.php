<?php

namespace DailyCheckinBundle\Tests\Event;

use DailyCheckinBundle\Event\BeforeReturnCheckinActivityEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class BeforeReturnCheckinActivityEventTest extends TestCase
{
    private BeforeReturnCheckinActivityEvent $event;

    protected function setUp(): void
    {
        $this->event = new BeforeReturnCheckinActivityEvent();
    }

    public function testGetSetResult(): void
    {
        $result = ['status' => 'success', 'data' => ['activity' => 'test']];
        
        $this->event->setResult($result);
        $this->assertSame($result, $this->event->getResult());
    }

    public function testGetSetUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        
        $this->event->setUser($user);
        $this->assertSame($user, $this->event->getUser());
    }

    public function testUserCanBeNull(): void
    {
        $this->assertNull($this->event->getUser());
        
        $this->event->setUser(null);
        $this->assertNull($this->event->getUser());
    }

    public function testInitialResultIsEmptyArray(): void
    {
        $this->assertSame([], $this->event->getResult());
    }
}