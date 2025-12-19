<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Tests\Param;

use DailyCheckinBundle\Param\GetCheckinAwardsParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * GetCheckinAwardsParam 单元测试
 *
 * @internal
 */
#[CoversClass(GetCheckinAwardsParam::class)]
final class GetCheckinAwardsParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new GetCheckinAwardsParam(activityId: 'test-activity');

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructor(): void
    {
        $param = new GetCheckinAwardsParam(activityId: 'activity-123');

        $this->assertSame('activity-123', $param->activityId);
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(GetCheckinAwardsParam::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPropertiesArePublicReadonly(): void
    {
        $reflection = new \ReflectionClass(GetCheckinAwardsParam::class);

        $properties = ['activityId'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "{$propertyName} should be public");
            $this->assertTrue($property->isReadOnly(), "{$propertyName} should be readonly");
        }
    }

    public function testHasMethodParamAttributes(): void
    {
        $reflection = new \ReflectionClass(GetCheckinAwardsParam::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $attrs = $parameter->getAttributes(\Tourze\JsonRPC\Core\Attribute\MethodParam::class);
            $this->assertNotEmpty($attrs, "Parameter {$parameter->getName()} should have MethodParam attribute");
        }
    }
}