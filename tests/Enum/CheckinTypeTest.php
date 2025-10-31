<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\CheckinType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CheckinType::class)]
final class CheckinTypeTest extends AbstractEnumTestCase
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
        $this->assertIsArray(CheckinType::CONTINUE->toArray());
        $this->assertIsArray(CheckinType::CONTINUE->toSelectItem());
    }

    public function testToArray(): void
    {
        $this->assertSame(['value' => 'continue', 'label' => '连续签到'], CheckinType::CONTINUE->toArray());
        $this->assertSame(['value' => 'accrued', 'label' => '累计签到'], CheckinType::ACCRUED->toArray());
    }
}
