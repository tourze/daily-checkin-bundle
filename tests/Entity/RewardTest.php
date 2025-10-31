<?php

namespace DailyCheckinBundle\Tests\Entity;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Reward::class)]
final class RewardTest extends AbstractEntityTestCase
{
    private Reward $reward;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reward = new Reward();
    }

    protected function createEntity(): Reward
    {
        return new Reward();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'sortNumber' => ['sortNumber', 1];
        yield 'name' => ['name', '测试奖品'];
        yield 'value' => ['value', '100积分'];
        yield 'times' => ['times', 3];
        yield 'quantity' => ['quantity', 10];
        yield 'dayLimit' => ['dayLimit', 5];
        yield 'isDefault' => ['isDefault', true];
        yield 'canShowPrize' => ['canShowPrize', true];
        yield 'beforePicture' => ['beforePicture', '/before.jpg'];
        yield 'afterPicture' => ['afterPicture', ['/after1.jpg', '/after2.jpg']];
        yield 'beforeButton' => ['beforeButton', '领取'];
        yield 'afterButton' => ['afterButton', '已领取'];
        yield 'remark' => ['remark', '测试奖品备注'];
        yield 'otherPicture' => ['otherPicture', ['/other1.jpg', '/other2.jpg']];
    }

    public function testGetSetName(): void
    {
        $name = '测试奖品';
        $this->reward->setName($name);
        $this->assertSame($name, $this->reward->getName());
    }

    public function testGetSetType(): void
    {
        $this->reward->setType(RewardType::CREDIT);
        $this->assertSame(RewardType::CREDIT, $this->reward->getType());

        $this->reward->setType(RewardType::COUPON);
        $this->assertSame(RewardType::COUPON, $this->reward->getType());
    }

    public function testGetSetValue(): void
    {
        $value = '100';
        $this->reward->setValue($value);
        $this->assertSame($value, $this->reward->getValue());
    }

    public function testGetSetTimes(): void
    {
        $times = 5;
        $this->reward->setTimes($times);
        $this->assertSame($times, $this->reward->getTimes());
    }

    public function testGetSetActivity(): void
    {
        $activity = new Activity();
        $activity->setTitle('测试活动');

        $this->reward->setActivity($activity);
        $this->assertSame($activity, $this->reward->getActivity());
    }

    public function testGetSetQuantity(): void
    {
        $quantity = 100;
        $this->reward->setQuantity($quantity);
        $this->assertSame($quantity, $this->reward->getQuantity());
    }

    public function testGetSetDayLimit(): void
    {
        $dayLimit = 10;
        $this->reward->setDayLimit($dayLimit);
        $this->assertSame($dayLimit, $this->reward->getDayLimit());
    }

    public function testGetSetIsDefault(): void
    {
        $this->reward->setIsDefault(true);
        $this->assertTrue($this->reward->getIsDefault());

        $this->reward->setIsDefault(false);
        $this->assertFalse($this->reward->getIsDefault());
    }

    public function testGetSetCanShowPrize(): void
    {
        $this->reward->setCanShowPrize(true);
        $this->assertTrue($this->reward->getCanShowPrize());

        $this->reward->setCanShowPrize(false);
        $this->assertFalse($this->reward->getCanShowPrize());
    }

    public function testGetSetRewardGetType(): void
    {
        $this->reward->setRewardGetType(RewardGetType::AND);
        $this->assertSame(RewardGetType::AND, $this->reward->getRewardGetType());

        $this->reward->setRewardGetType(RewardGetType::OR);
        $this->assertSame(RewardGetType::OR, $this->reward->getRewardGetType());
    }

    public function testGetSetRemark(): void
    {
        $remark = '测试备注';
        $this->reward->setRemark($remark);
        $this->assertSame($remark, $this->reward->getRemark());
    }

    public function testAddRemoveAward(): void
    {
        $award = new Award();

        $this->reward->addAward($award);
        $this->assertTrue($this->reward->getAwards()->contains($award));
        $this->assertSame($this->reward, $award->getReward());

        $this->reward->removeAward($award);
        $this->assertFalse($this->reward->getAwards()->contains($award));
    }

    public function testToStringWithoutId(): void
    {
        $name = '测试奖品';
        $this->reward->setName($name);
        $this->reward->setType(RewardType::CREDIT);
        $this->reward->setValue('100');
        $this->reward->setTimes(1);

        // 当 ID 为空时，__toString() 应返回空字符串
        $this->assertSame('', (string) $this->reward);
    }

    public function testToStringWithAllProperties(): void
    {
        $name = '测试奖品';
        $this->reward->setName($name);
        $this->reward->setType(RewardType::CREDIT);
        $this->reward->setValue('100');
        $this->reward->setTimes(1);

        // 当没有ID时，toString应该返回空字符串
        $this->assertSame('', (string) $this->reward);

        // 测试Reward的属性是否正确设置
        $this->assertSame($name, $this->reward->getName());
        $this->assertSame(RewardType::CREDIT, $this->reward->getType());
        $this->assertSame('100', $this->reward->getValue());
        $this->assertSame(1, $this->reward->getTimes());
    }
}
