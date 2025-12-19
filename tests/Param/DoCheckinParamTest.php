<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Tests\Param;

use DailyCheckinBundle\Param\DoCheckinParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * DoCheckinParam 单元测试
 *
 * @internal
 */
#[CoversClass(DoCheckinParam::class)]
final class DoCheckinParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new DoCheckinParam(activityId: 'test-activity');

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithRequiredParameterOnly(): void
    {
        $param = new DoCheckinParam(activityId: 'activity-123');

        $this->assertSame('activity-123', $param->activityId);
        $this->assertSame('', $param->checkinDate);
    }

    public function testConstructorWithAllParameters(): void
    {
        $param = new DoCheckinParam(
            activityId: 'activity-456',
            checkinDate: '2025-01-01',
        );

        $this->assertSame('activity-456', $param->activityId);
        $this->assertSame('2025-01-01', $param->checkinDate);
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(DoCheckinParam::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPropertiesArePublicReadonly(): void
    {
        $reflection = new \ReflectionClass(DoCheckinParam::class);

        $properties = ['activityId', 'checkinDate'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "{$propertyName} should be public");
            $this->assertTrue($property->isReadOnly(), "{$propertyName} should be readonly");
        }
    }

    public function testHasMethodParamAttributes(): void
    {
        $reflection = new \ReflectionClass(DoCheckinParam::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $attrs = $parameter->getAttributes(\Tourze\JsonRPC\Core\Attribute\MethodParam::class);
            $this->assertNotEmpty($attrs, "Parameter {$parameter->getName()} should have MethodParam attribute");
        }
    }
}