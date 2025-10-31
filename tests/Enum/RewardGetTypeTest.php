<?php

namespace DailyCheckinBundle\Tests\Enum;

use DailyCheckinBundle\Enum\RewardGetType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(RewardGetType::class)]
final class RewardGetTypeTest extends AbstractEnumTestCase
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
        $this->assertIsArray(RewardGetType::AND->toArray());
        $this->assertIsArray(RewardGetType::AND->toSelectItem());
    }

    public function testToArray(): void
    {
        $this->assertSame(['value' => 'and', 'label' => '并列'], RewardGetType::AND->toArray());
        $this->assertSame(['value' => 'or', 'label' => '或者'], RewardGetType::OR->toArray());
    }
}
