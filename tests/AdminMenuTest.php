<?php

namespace DailyCheckinBundle\Tests;

use DailyCheckinBundle\AdminMenu;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface $linkGenerator;
    private ItemInterface $menuItem;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->menuItem = $this->createMock(ItemInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testInstanceOfMenuProvider(): void
    {
        $this->assertInstanceOf('Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface', $this->adminMenu);
    }

    public function testInvokeMethodExists(): void
    {
        $checkinMenuItem = $this->createMock(ItemInterface::class);
        
        // 基本模拟设置
        $this->menuItem->method('getChild')->willReturn($checkinMenuItem);
        $this->menuItem->method('addChild')->willReturn($checkinMenuItem);
        $checkinMenuItem->method('addChild')->willReturnSelf();
        $checkinMenuItem->method('setUri')->willReturnSelf();
        $this->linkGenerator->method('getCurdListPage')->willReturn('/admin/test');
        
        // 测试调用不会抛出异常
        ($this->adminMenu)($this->menuItem);
        $this->assertTrue(true);
    }
}