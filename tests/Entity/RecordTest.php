<?php

namespace DailyCheckinBundle\Tests\Entity;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class RecordTest extends TestCase
{
    private Record $record;

    protected function setUp(): void
    {
        $this->record = new Record();
    }

    public function testGetSetActivity(): void
    {
        $activity = new Activity();
        $activity->setTitle('测试活动');
        
        $this->record->setActivity($activity);
        $this->assertSame($activity, $this->record->getActivity());
    }

    public function testGetSetUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        
        $this->record->setUser($user);
        $this->assertSame($user, $this->record->getUser());
    }

    public function testGetSetCheckinDate(): void
    {
        $checkinDate = new \DateTime('2025-06-29 10:00:00');
        $this->record->setCheckinDate($checkinDate);
        $this->assertSame($checkinDate, $this->record->getCheckinDate());
    }

    public function testGetSetCheckinTimes(): void
    {
        $checkinTimes = 7;
        $this->record->setCheckinTimes($checkinTimes);
        $this->assertSame($checkinTimes, $this->record->getCheckinTimes());
    }

    public function testGetSetRemark(): void
    {
        $remark = '签到成功';
        $this->record->setRemark($remark);
        $this->assertSame($remark, $this->record->getRemark());
    }

    public function testGetSetHasAward(): void
    {
        $this->record->setHasAward(true);
        $this->assertTrue($this->record->hasAward());

        $this->record->setHasAward(false);
        $this->assertFalse($this->record->hasAward());
    }

    public function testAddRemoveAward(): void
    {
        $award = new Award();

        $this->record->addAward($award);
        $this->assertTrue($this->record->getAwards()->contains($award));
        $this->assertSame($this->record, $award->getRecord());

        $this->record->removeAward($award);
        $this->assertFalse($this->record->getAwards()->contains($award));
    }

    public function testInitialAwardsCollectionIsEmpty(): void
    {
        $this->assertCount(0, $this->record->getAwards());
    }
}