<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinAwardCrudController;
use DailyCheckinBundle\Entity\Award;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;

class DailyCheckinAwardCrudControllerTest extends TestCase
{
    private DailyCheckinAwardCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new DailyCheckinAwardCrudController();
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(DailyCheckinAwardCrudController::class, $this->controller);
    }

    public function testExtendsAbstractCrudController(): void
    {
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Award::class, $this->controller::getEntityFqcn());
    }
}