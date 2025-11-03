<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinActivityCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinActivityCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DailyCheckinActivityCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): DailyCheckinActivityCrudController
    {
        return self::getService(DailyCheckinActivityCrudController::class);
    }

    protected function getControllerServiceWithType(): DailyCheckinActivityCrudController
    {
        return self::getService(DailyCheckinActivityCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '标题' => ['标题'];
        yield '是否启用此活动' => ['是否启用此活动'];
        yield '开始时间' => ['开始时间'];
        yield '结束时间' => ['结束时间'];
        yield '签到次数' => ['签到次数'];
        yield '签到类型' => ['签到类型'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'valid' => ['valid'];
        yield 'startTime' => ['startTime'];
        yield 'endTime' => ['endTime'];
        yield 'times' => ['times'];
        yield 'checkinType' => ['checkinType'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'valid' => ['valid'];
        yield 'startTime' => ['startTime'];
        yield 'endTime' => ['endTime'];
        yield 'times' => ['times'];
        yield 'checkinType' => ['checkinType'];
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new DailyCheckinActivityCrudController();
        $this->assertSame('DailyCheckinBundle\Entity\Activity', $controller::getEntityFqcn());
    }

    public function testBasicAdminAccessWorks(): void
    {
        $client = self::createAuthenticatedClient();

        // Test basic admin access works
        $client->request('GET', '/admin');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
