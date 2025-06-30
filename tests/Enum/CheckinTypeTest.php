<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\CheckinType;
use PHPUnit\Framework\TestCase;

class CheckinTypeTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('continue', CheckinType::CONTINUE->value);
        $this->assertSame('accrued', CheckinType::ACCRUED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('连续签到', CheckinType::CONTINUE->getLabel());
        $this->assertSame('累计签到', CheckinType::ACCRUED->getLabel());
    }

    public function testCases(): void
    {
        $cases = CheckinType::cases();
        $this->assertCount(2, $cases);
        $this->assertContains(CheckinType::CONTINUE, $cases);
        $this->assertContains(CheckinType::ACCRUED, $cases);
    }

    public function testTraitsAreUsed(): void
    {
        $reflection = new \ReflectionClass(CheckinType::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
    }
}