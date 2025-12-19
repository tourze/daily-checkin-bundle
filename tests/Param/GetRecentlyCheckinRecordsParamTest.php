<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Tests\Param;

use DailyCheckinBundle\Param\GetRecentlyCheckinRecordsParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * GetRecentlyCheckinRecordsParam 单元测试
 *
 * @internal
 */
#[CoversClass(GetRecentlyCheckinRecordsParam::class)]
final class GetRecentlyCheckinRecordsParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new GetRecentlyCheckinRecordsParam(activityId: 'test-activity');

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithRequiredParameterOnly(): void
    {
        $param = new GetRecentlyCheckinRecordsParam(activityId: 'activity-123');

        $this->assertSame('activity-123', $param->activityId);
        $this->assertSame(4, $param->nums);
    }

    public function testConstructorWithAllParameters(): void
    {
        $param = new GetRecentlyCheckinRecordsParam(
            activityId: 'activity-456',
            nums: 10,
        );

        $this->assertSame('activity-456', $param->activityId);
        $this->assertSame(10, $param->nums);
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(GetRecentlyCheckinRecordsParam::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPropertiesArePublicReadonly(): void
    {
        $reflection = new \ReflectionClass(GetRecentlyCheckinRecordsParam::class);

        $properties = ['activityId', 'nums'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "{$propertyName} should be public");
            $this->assertTrue($property->isReadOnly(), "{$propertyName} should be readonly");
        }
    }

    public function testHasMethodParamAttributes(): void
    {
        $reflection = new \ReflectionClass(GetRecentlyCheckinRecordsParam::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $attrs = $parameter->getAttributes(\Tourze\JsonRPC\Core\Attribute\MethodParam::class);
            $this->assertNotEmpty($attrs, "Parameter {$parameter->getName()} should have MethodParam attribute");
        }
    }
}