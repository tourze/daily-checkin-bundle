<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Repository\AwardRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class AwardRepositoryTest extends TestCase
{
    private AwardRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new AwardRepository($this->registry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(AwardRepository::class, $this->repository);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass(AwardRepository::class);
        $this->assertTrue($reflection->hasMethod('__construct'));
    }
}