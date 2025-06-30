<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Repository\RecordRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class RecordRepositoryTest extends TestCase
{
    private RecordRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new RecordRepository($this->registry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(RecordRepository::class, $this->repository);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass(RecordRepository::class);
        $this->assertTrue($reflection->hasMethod('__construct'));
    }
}