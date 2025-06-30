<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\RewardGetType;
use PHPUnit\Framework\TestCase;

class RewardGetTypeTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('and', RewardGetType::AND->value);
        $this->assertSame('or', RewardGetType::OR->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('并列', RewardGetType::AND->getLabel());
        $this->assertSame('或者', RewardGetType::OR->getLabel());
    }

    public function testCases(): void
    {
        $cases = RewardGetType::cases();
        $this->assertCount(2, $cases);
        $this->assertContains(RewardGetType::AND, $cases);
        $this->assertContains(RewardGetType::OR, $cases);
    }

    public function testTraitsAreUsed(): void
    {
        $reflection = new \ReflectionClass(RewardGetType::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
    }
}