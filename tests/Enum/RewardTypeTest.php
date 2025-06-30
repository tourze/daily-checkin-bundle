<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\RewardType;
use PHPUnit\Framework\TestCase;

class RewardTypeTest extends TestCase
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
        $reflection = new \ReflectionClass(RewardType::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
    }
}