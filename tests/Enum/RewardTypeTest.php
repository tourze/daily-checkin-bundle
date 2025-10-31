<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\RewardType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(RewardType::class)]
final class RewardTypeTest extends AbstractEnumTestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('coupon', RewardType::COUPON->value);
        $this->assertSame('credit', RewardType::CREDIT->value);
        $this->assertSame('badge', RewardType::BADGE->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('优惠券', RewardType::COUPON->getLabel());
        $this->assertSame('积分', RewardType::CREDIT->getLabel());
        $this->assertSame('徽章', RewardType::BADGE->getLabel());
    }

    public function testCases(): void
    {
        $cases = RewardType::cases();
        $this->assertCount(3, $cases);
        $this->assertContains(RewardType::COUPON, $cases);
        $this->assertContains(RewardType::CREDIT, $cases);
        $this->assertContains(RewardType::BADGE, $cases);
    }

    public function testTraitsAreUsed(): void
    {
        $this->assertIsArray(RewardType::COUPON->toArray());
        $this->assertIsArray(RewardType::COUPON->toSelectItem());
    }

    public function testToArray(): void
    {
        $this->assertSame(['value' => 'coupon', 'label' => '优惠券'], RewardType::COUPON->toArray());
        $this->assertSame(['value' => 'credit', 'label' => '积分'], RewardType::CREDIT->toArray());
        $this->assertSame(['value' => 'badge', 'label' => '徽章'], RewardType::BADGE->toArray());
    }
}
