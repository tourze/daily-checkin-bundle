<?php

namespace DailyCheckinBundle\Tests\Entity;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class AwardTest extends TestCase
{
    private Award $award;

    protected function setUp(): void
    {
        $this->award = new Award();
    }

    public function testGetSetRecord(): void
    {
        $record = new Record();
        $record->setCheckinDate(new \DateTime());
        
        $this->award->setRecord($record);
        $this->assertSame($record, $this->award->getRecord());
    }

    public function testGetSetReward(): void
    {
        $reward = new Reward();
        $reward->setName('测试奖品');
        $reward->setType(RewardType::CREDIT);
        $reward->setValue('100');
        
        $this->award->setReward($reward);
        $this->assertSame($reward, $this->award->getReward());
    }

    public function testGetSetUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        
        $this->award->setUser($user);
        $this->assertSame($user, $this->award->getUser());
    }

    public function testInitialValuesAreNull(): void
    {
        $this->assertNull($this->award->getRecord());
        $this->assertNull($this->award->getReward());
        $this->assertNull($this->award->getUser());
    }
}