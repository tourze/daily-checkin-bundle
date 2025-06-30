<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Repository\RewardRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class RewardRepositoryTest extends TestCase
{
    private RewardRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new RewardRepository($this->registry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(RewardRepository::class, $this->repository);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass(RewardRepository::class);
        $this->assertTrue($reflection->hasMethod('__construct'));
    }
}