<?php

namespace DailyCheckinBundle\Tests\Controller\Admin;

use DailyCheckinBundle\Controller\Admin\DailyCheckinRecordCrudController;
use DailyCheckinBundle\Entity\Record;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;

class DailyCheckinRecordCrudControllerTest extends TestCase
{
    private DailyCheckinRecordCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new DailyCheckinRecordCrudController();
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(DailyCheckinRecordCrudController::class, $this->controller);
    }

    public function testExtendsAbstractCrudController(): void
    {
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Record::class, $this->controller::getEntityFqcn());
    }
}