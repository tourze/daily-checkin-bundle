<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Tests\Param;

use DailyCheckinBundle\Param\SubmitCheckinAwardParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * SubmitCheckinAwardParam 单元测试
 *
 * @internal
 */
#[CoversClass(SubmitCheckinAwardParam::class)]
final class SubmitCheckinAwardParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new SubmitCheckinAwardParam();

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $param = new SubmitCheckinAwardParam();

        $this->assertSame('', $param->rewardId);
        $this->assertSame('', $param->recordId);
    }

    public function testConstructorWithCustomValues(): void
    {
        $param = new SubmitCheckinAwardParam(
            rewardId: 'reward-123',
            recordId: 'record-456',
        );

        $this->assertSame('reward-123', $param->rewardId);
        $this->assertSame('record-456', $param->recordId);
    }

    public function testConstructorWithPartialValues(): void
    {
        $param = new SubmitCheckinAwardParam(
            rewardId: 'reward-789',
        );

        $this->assertSame('reward-789', $param->rewardId);
        $this->assertSame('', $param->recordId);
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(SubmitCheckinAwardParam::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPropertiesArePublicReadonly(): void
    {
        $reflection = new \ReflectionClass(SubmitCheckinAwardParam::class);

        $properties = ['rewardId', 'recordId'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "{$propertyName} should be public");
            $this->assertTrue($property->isReadOnly(), "{$propertyName} should be readonly");
        }
    }

    public function testHasNoMethodParamAttributes(): void
    {
        $reflection = new \ReflectionClass(SubmitCheckinAwardParam::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $attrs = $parameter->getAttributes(\Tourze\JsonRPC\Core\Attribute\MethodParam::class);
            $this->assertEmpty($attrs, "Parameter {$parameter->getName()} should not have MethodParam attribute");
        }
    }
}