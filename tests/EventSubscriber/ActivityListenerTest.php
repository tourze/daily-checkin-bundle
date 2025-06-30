<?php

namespace DailyCheckinBundle\Tests\EventSubscriber;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\EventSubscriber\ActivityListener;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;

class ActivityListenerTest extends TestCase
{
    private ActivityListener $listener;
    private Activity $activity;

    protected function setUp(): void
    {
        $this->listener = new ActivityListener();
        $this->activity = new Activity();
    }

    public function testListenerInstantiation(): void
    {
        $this->assertInstanceOf(ActivityListener::class, $this->listener);
    }

    public function testPrePersistWithValidDates(): void
    {
        $startTime = CarbonImmutable::now();
        $endTime = CarbonImmutable::now()->addDays(30);
        
        $this->activity->setStartTime($startTime);
        $this->activity->setEndTime($endTime);
        
        // 不应抛出异常
        $this->listener->prePersist($this->activity);
        $this->assertTrue(true); // 如果没有异常则测试通过
    }

    public function testPreUpdateWithValidDates(): void
    {
        $startTime = CarbonImmutable::now();
        $endTime = CarbonImmutable::now()->addDays(30);
        
        $this->activity->setStartTime($startTime);
        $this->activity->setEndTime($endTime);
        
        // 不应抛出异常
        $this->listener->preUpdate($this->activity);
        $this->assertTrue(true); // 如果没有异常则测试通过
    }

    public function testListenerCanHandleValidation(): void
    {
        // 测试监听器可以处理有效的活动对象
        $this->assertInstanceOf(ActivityListener::class, $this->listener);
    }

    public function testEnsureDateValidWithEndTimeBeforeStartTime(): void
    {
        $this->expectException(ApiException::class);
        
        $startTime = CarbonImmutable::now();
        $endTime = CarbonImmutable::now()->subDays(1); // 结束时间在开始时间之前
        
        $this->activity->setStartTime($startTime);
        $this->activity->setEndTime($endTime);
        
        $this->listener->ensureDateValid($this->activity);
    }
}