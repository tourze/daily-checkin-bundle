<?php

namespace DailyCheckinBundle\Tests\Procedure;

use DailyCheckinBundle\Procedure\DoCheckin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

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

    public function testActivityIdPropertyCanBeSet(): void
    {
        $testActivityId = 'test-activity-id-' . uniqid();
        $this->procedure->activityId = $testActivityId;

        $this->assertSame($testActivityId, $this->procedure->activityId);
    }
}
