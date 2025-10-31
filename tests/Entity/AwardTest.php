<?php

namespace DailyCheckinBundle\Tests\Entity;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Award::class)]
final class AwardTest extends AbstractEntityTestCase
{
    private Award $award;

    protected function setUp(): void
    {
        parent::setUp();

        $this->award = new Award();
    }

    protected function createEntity(): Award
    {
        return new Award();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $record = new Record();
        $record->setCheckinDate(new \DateTime());

        $reward = new Reward();
        $reward->setName('测试奖品');
        $reward->setType(RewardType::CREDIT);
        $reward->setValue('100');

        yield 'record' => ['record', $record];
        yield 'reward' => ['reward', $reward];
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
        $user = new InMemoryUser('test@example.com', null);

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
