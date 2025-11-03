<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinAwardCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinAwardCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DailyCheckinAwardCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): DailyCheckinAwardCrudController
    {
        return self::getService(DailyCheckinAwardCrudController::class);
    }

    protected function getControllerServiceWithType(): DailyCheckinAwardCrudController
    {
        return self::getService(DailyCheckinAwardCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '用户' => ['用户'];
        yield '签到记录' => ['签到记录'];
        yield '奖品' => ['奖品'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'user' => ['user'];
        yield 'record' => ['record'];
        yield 'reward' => ['reward'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'user' => ['user'];
        yield 'record' => ['record'];
        yield 'reward' => ['reward'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new DailyCheckinAwardCrudController();
        $this->assertSame('DailyCheckinBundle\Entity\Award', $controller::getEntityFqcn());
    }

    public function testBasicAdminAccessWorks(): void
    {
        $client = self::createAuthenticatedClient();

        // Test basic admin access works
        $client->request('GET', '/admin');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
