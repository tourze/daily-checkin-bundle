<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Repository\ActivityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ActivityRepositoryTest extends TestCase
{
    private ActivityRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new ActivityRepository($this->registry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(ActivityRepository::class, $this->repository);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass(ActivityRepository::class);
        $this->assertTrue($reflection->hasMethod('__construct'));
    }
}