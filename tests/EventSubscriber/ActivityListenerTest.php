<?php

namespace DailyCheckinBundle\Tests\EventSubscriber;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\EventSubscriber\ActivityListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ActivityListener::class)]
#[RunTestsInSeparateProcesses]
final class ActivityListenerTest extends AbstractIntegrationTestCase
{
    private ActivityListener $listener;

    private Activity $activity;

    protected function onSetUp(): void
    {
        $this->listener = self::getService(ActivityListener::class);
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

        // 验证活动的开始和结束时间设置正确
        $this->assertSame($startTime, $this->activity->getStartTime());
        $this->assertSame($endTime, $this->activity->getEndTime());

        // 调用监听器方法不应抛出异常
        $this->listener->prePersist($this->activity);

        // 验证时间仍然有效
        $this->assertLessThan($this->activity->getEndTime(), $this->activity->getStartTime());
    }

    public function testPreUpdateWithValidDates(): void
    {
        $startTime = CarbonImmutable::now();
        $endTime = CarbonImmutable::now()->addDays(30);

        $this->activity->setStartTime($startTime);
        $this->activity->setEndTime($endTime);

        // 验证活动的开始和结束时间设置正确
        $this->assertSame($startTime, $this->activity->getStartTime());
        $this->assertSame($endTime, $this->activity->getEndTime());

        // 调用监听器方法不应抛出异常
        $this->listener->preUpdate($this->activity);

        // 验证时间仍然有效
        $this->assertLessThan($this->activity->getEndTime(), $this->activity->getStartTime());
    }

    public function testListenerCanHandleValidation(): void
    {
        // 测试监听器可以处理有效的活动对象
        $this->assertInstanceOf(ActivityListener::class, $this->listener);
    }

    public function testEnsureDateValidWithEndTimeBeforeStartTime(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $startTime = CarbonImmutable::now();
        $endTime = CarbonImmutable::now()->subDays(1); // 结束时间在开始时间之前

        $this->activity->setStartTime($startTime);
        $this->activity->setEndTime($endTime);

        $this->listener->ensureDateValid($this->activity);
    }
}
