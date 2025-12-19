<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Param\DoCheckinParam;
use DailyCheckinBundle\Procedure\DoCheckin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(DoCheckin::class)]
#[RunTestsInSeparateProcesses]
final class DoCheckinTest extends AbstractProcedureTestCase
{
    private DoCheckin $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(DoCheckin::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(DoCheckin::class, $this->procedure);
    }

    public function testImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf('Tourze\JsonRPCLockBundle\Procedure\LockableProcedure', $this->procedure);
        $this->assertInstanceOf('Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure', $this->procedure);
    }

    public function testGenerateFormattedLogText(): void
    {
        $request = new JsonRpcRequest();
        $result = $this->procedure->generateFormattedLogText($request);
        $this->assertSame('打卡签到', $result);
    }

    public function testExecuteMethodIsCallable(): void
    {
        // 验证 execute 方法为 public 可调用
        $reflection = new \ReflectionMethod($this->procedure, 'execute');
        $this->assertTrue($reflection->isPublic());
    }

    public function testExecuteWithDoCheckinParam(): void
    {
        $param = new DoCheckinParam(
            activityId: 'test-activity-id-' . uniqid(),
            checkinDate: ''
        );

        // 验证参数可以正确传递给 execute 方法
        $reflection = new \ReflectionMethod($this->procedure, 'execute');
        $this->assertTrue($reflection->isPublic());
        $parameters = $reflection->getParameters();
        $this->assertCount(1, $parameters);
        $paramType = $parameters[0]->getType();
        $this->assertNotNull($paramType);

        // 验证类型包含 DoCheckinParam
        $typeString = $paramType->__toString();
        $this->assertStringContainsString(DoCheckinParam::class, $typeString);
        $this->assertStringContainsString('RpcParamInterface', $typeString);
    }
}
