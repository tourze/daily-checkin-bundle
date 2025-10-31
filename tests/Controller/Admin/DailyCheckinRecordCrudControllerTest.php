<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinRecordCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinRecordCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DailyCheckinRecordCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): DailyCheckinRecordCrudController
    {
        return self::getService(DailyCheckinRecordCrudController::class);
    }

    protected function getControllerServiceWithType(): DailyCheckinRecordCrudController
    {
        return self::getService(DailyCheckinRecordCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '用户' => ['用户'];
        yield '活动' => ['活动'];
        yield '签到日期' => ['签到日期'];
        yield '连续签到次数' => ['连续签到次数'];
        yield '是否有奖' => ['是否有奖'];
        yield '备注' => ['备注'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'user' => ['user'];
        yield 'activity' => ['activity'];
        yield 'checkinDate' => ['checkinDate'];
        yield 'checkinTimes' => ['checkinTimes'];
        yield 'hasAward' => ['hasAward'];
        yield 'remark' => ['remark'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'user' => ['user'];
        yield 'activity' => ['activity'];
        yield 'checkinDate' => ['checkinDate'];
        yield 'checkinTimes' => ['checkinTimes'];
        yield 'hasAward' => ['hasAward'];
        yield 'remark' => ['remark'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    protected function onSetUp(): void
    {
        // Setup for EasyAdmin controller tests
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new DailyCheckinRecordCrudController();
        $this->assertSame('DailyCheckinBundle\Entity\Record', $controller::getEntityFqcn());
    }

    public function testBasicAdminAccessWorks(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // Test basic admin access works
        $client->request('GET', '/admin');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
