<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinRewardCrudController;
use DailyCheckinBundle\Entity\Reward;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;

class DailyCheckinRewardCrudControllerTest extends TestCase
{
    private DailyCheckinRewardCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new DailyCheckinRewardCrudController();
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(DailyCheckinRewardCrudController::class, $this->controller);
    }

    public function testExtendsAbstractCrudController(): void
    {
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Reward::class, $this->controller::getEntityFqcn());
    }
}