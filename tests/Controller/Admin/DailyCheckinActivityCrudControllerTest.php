<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinActivityCrudController;
use DailyCheckinBundle\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;

class DailyCheckinActivityCrudControllerTest extends TestCase
{
    private DailyCheckinActivityCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new DailyCheckinActivityCrudController();
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(DailyCheckinActivityCrudController::class, $this->controller);
    }

    public function testExtendsAbstractCrudController(): void
    {
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Activity::class, $this->controller::getEntityFqcn());
    }
}