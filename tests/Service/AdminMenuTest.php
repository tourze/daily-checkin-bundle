<?php

namespace DailyCheckinBundle\Tests\Service;

use DailyCheckinBundle\Service\AdminMenu;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        // 从容器中获取 AdminMenu 服务
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInstanceOfMenuProvider(): void
    {
        $this->assertInstanceOf('Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface', $this->adminMenu);
    }

    public function testInvokeMethodExists(): void
    {
        // 创建工厂实例
        $factory = new MenuFactory();

        // 创建真实的菜单项，而不是使用复杂的 Mock
        $rootMenuItem = new MenuItem('root-menu', $factory);

        // 调用AdminMenu的__invoke方法
        ($this->adminMenu)($rootMenuItem);

        // 验证签到打卡主菜单是否被添加
        $checkinMenuItem = $rootMenuItem->getChild('签到打卡');
        $this->assertNotNull($checkinMenuItem, 'Checkin menu item should be added');

        // 验证四个子菜单项是否被添加
        $expectedChildNames = ['打卡活动', '签到记录', '奖品设置', '奖励情况'];
        foreach ($expectedChildNames as $expectedName) {
            $childMenuItem = $checkinMenuItem->getChild($expectedName);
            $this->assertNotNull($childMenuItem, "Child menu item '{$expectedName}' should be added");
            $this->assertNotNull($childMenuItem->getUri(), "Child menu item '{$expectedName}' should have URI set");
        }

        // 验证子菜单项数量
        $this->assertCount(4, $checkinMenuItem->getChildren(), 'Checkin menu should have exactly 4 children');
    }
}
