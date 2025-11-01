<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinRewardCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinRewardCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DailyCheckinRewardCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): DailyCheckinRewardCrudController
    {
        return self::getService(DailyCheckinRewardCrudController::class);
    }

    protected function getControllerServiceWithType(): DailyCheckinRewardCrudController
    {
        return self::getService(DailyCheckinRewardCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '奖品名称' => ['奖品名称'];
        yield '奖品类型' => ['奖品类型'];
        yield '奖项值' => ['奖项值'];
        yield '签到次数' => ['签到次数'];
        yield '总数量' => ['总数量'];
        yield '每日限制' => ['每日限制'];
        yield '排序' => ['排序'];
        yield '是否兜底' => ['是否兜底'];
        yield '是否展示' => ['是否展示'];
        yield '获取方式' => ['获取方式'];
        yield '活动' => ['活动'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'value' => ['value'];
        yield 'times' => ['times'];
        yield 'quantity' => ['quantity'];
        yield 'dayLimit' => ['dayLimit'];
        yield 'sortNumber' => ['sortNumber'];
        yield 'isDefault' => ['isDefault'];
        yield 'canShowPrize' => ['canShowPrize'];
        yield 'rewardGetType' => ['rewardGetType'];
        yield 'activity' => ['activity'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'value' => ['value'];
        yield 'times' => ['times'];
        yield 'quantity' => ['quantity'];
        yield 'dayLimit' => ['dayLimit'];
        yield 'sortNumber' => ['sortNumber'];
        yield 'isDefault' => ['isDefault'];
        yield 'canShowPrize' => ['canShowPrize'];
        yield 'rewardGetType' => ['rewardGetType'];
        yield 'activity' => ['activity'];
        yield 'createTime' => ['createTime'];
        yield 'updateTime' => ['updateTime'];
    }

    protected function onSetUp(): void
    {
        // Setup for EasyAdmin controller tests
    }

    public function testControllerInstanceCreation(): void
    {
        $controller = new DailyCheckinRewardCrudController();
        $this->assertSame('DailyCheckinBundle\Entity\Reward', $controller::getEntityFqcn());
    }

    public function testBasicAdminAccessWorks(): void
    {
        $client = self::createAuthenticatedClient();

        // Test basic admin access works
        $client->request('GET', '/admin');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
