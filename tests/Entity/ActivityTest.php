<?php

namespace DailyCheckinBundle\Tests\Entity;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    private Activity $activity;

    protected function setUp(): void
    {
        $this->activity = new Activity();
    }

    public function testGetSetTitle(): void
    {
        $title = '测试签到活动';
        $this->activity->setTitle($title);
        $this->assertSame($title, $this->activity->getTitle());
    }

    public function testGetSetStartTime(): void
    {
        $startTime = new \DateTime('2025-01-01 00:00:00');
        $this->activity->setStartTime($startTime);
        $this->assertSame($startTime, $this->activity->getStartTime());
    }

    public function testGetSetEndTime(): void
    {
        $endTime = new \DateTime('2025-12-31 23:59:59');
        $this->activity->setEndTime($endTime);
        $this->assertSame($endTime, $this->activity->getEndTime());
    }

    public function testGetSetTimes(): void
    {
        $times = 30;
        $this->activity->setTimes($times);
        $this->assertSame($times, $this->activity->getTimes());
    }

    public function testGetSetValid(): void
    {
        $this->activity->setValid(true);
        $this->assertTrue($this->activity->isValid());

        $this->activity->setValid(false);
        $this->assertFalse($this->activity->isValid());
    }

    public function testGetSetCheckinType(): void
    {
        $this->activity->setCheckinType(CheckinType::CONTINUE);
        $this->assertSame(CheckinType::CONTINUE, $this->activity->getCheckinType());

        $this->activity->setCheckinType(CheckinType::ACCRUED);
        $this->assertSame(CheckinType::ACCRUED, $this->activity->getCheckinType());
    }

    public function testGetSetSharePath(): void
    {
        $sharePath = '/share/activity/123';
        $this->activity->setSharePath($sharePath);
        $this->assertSame($sharePath, $this->activity->getSharePath());
    }

    public function testGetSetShareTitle(): void
    {
        $shareTitle = '分享标题';
        $this->activity->setShareTitle($shareTitle);
        $this->assertSame($shareTitle, $this->activity->getShareTitle());
    }

    public function testGetSetSharePicture(): void
    {
        $sharePicture = '/images/share.jpg';
        $this->activity->setSharePicture($sharePicture);
        $this->assertSame($sharePicture, $this->activity->getSharePicture());
    }

    public function testAddRemoveReward(): void
    {
        $reward = new Reward();
        $reward->setName('测试奖品');

        $this->activity->addReward($reward);
        $this->assertTrue($this->activity->getRewards()->contains($reward));
        $this->assertSame($this->activity, $reward->getActivity());

        $this->activity->removeReward($reward);
        $this->assertFalse($this->activity->getRewards()->contains($reward));
    }

    public function testToStringWithoutId(): void
    {
        $title = '测试活动';
        $this->activity->setTitle($title);
        // 当 ID 为 null 时，__toString() 应返回空字符串
        $this->assertSame('', (string) $this->activity);
    }

    public function testToStringWithId(): void
    {
        $title = '测试活动';
        $this->activity->setTitle($title);
        
        // 使用反射设置一个 ID
        $reflection = new \ReflectionProperty($this->activity, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($this->activity, '123456789');
        
        $this->assertSame($title, (string) $this->activity);
    }
}