<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Repository\AwardRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template-extends AbstractRepositoryTestCase<Award>
 * @internal
 */
#[CoversClass(AwardRepository::class)]
#[RunTestsInSeparateProcesses]
final class AwardRepositoryTest extends AbstractRepositoryTestCase
{
    private AwardRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AwardRepository::class);
    }

    public function testFindByWithNullableRecord(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest();
        $this->persistAndFlush($record);

        $award1 = $this->createAwardForTest(['record' => $record]);
        $award2 = $this->createAwardForTest(['record' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $resultsWithRecord = $this->repository->findBy(['record' => $record]);
        $resultsWithNullRecord = $this->repository->findBy(['record' => null]);

        $this->assertCount(1, $resultsWithRecord);
        $this->assertSame($record, $resultsWithRecord[0]->getRecord());
        $this->assertCount(1, $resultsWithNullRecord);
        $this->assertNull($resultsWithNullRecord[0]->getRecord());
    }

    public function testFindByWithNullableReward(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward = $this->createRewardForTest();
        $this->persistAndFlush($reward);

        $award1 = $this->createAwardForTest(['reward' => $reward]);
        $award2 = $this->createAwardForTest(['reward' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $resultsWithReward = $this->repository->findBy(['reward' => $reward]);
        $resultsWithNullReward = $this->repository->findBy(['reward' => null]);

        $this->assertCount(1, $resultsWithReward);
        $this->assertSame($reward, $resultsWithReward[0]->getReward());
        $this->assertCount(1, $resultsWithNullReward);
        $this->assertNull($resultsWithNullReward[0]->getReward());
    }

    public function testFindByWithNullableUser(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $user = $this->createUserForTest();

        $award1 = $this->createAwardForTest(['user' => $user]);
        $award2 = $this->createAwardForTest(['user' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $resultsWithUser = $this->repository->findBy(['user' => $user]);
        $resultsWithNullUser = $this->repository->findBy(['user' => null]);

        $this->assertCount(1, $resultsWithUser);
        $this->assertSame($user, $resultsWithUser[0]->getUser());
        $this->assertCount(1, $resultsWithNullUser);
        $this->assertNull($resultsWithNullUser[0]->getUser());
    }

    public function testCountWithNullableRecord(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest();
        $this->persistAndFlush($record);

        $award1 = $this->createAwardForTest(['record' => $record]);
        $award2 = $this->createAwardForTest(['record' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $countWithRecord = $this->repository->count(['record' => $record]);
        $countWithNullRecord = $this->repository->count(['record' => null]);

        $this->assertEquals(1, $countWithRecord);
        $this->assertEquals(1, $countWithNullRecord);
    }

    public function testCountWithNullableReward(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward = $this->createRewardForTest();
        $this->persistAndFlush($reward);

        $award1 = $this->createAwardForTest(['reward' => $reward]);
        $award2 = $this->createAwardForTest(['reward' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $countWithReward = $this->repository->count(['reward' => $reward]);
        $countWithNullReward = $this->repository->count(['reward' => null]);

        $this->assertEquals(1, $countWithReward);
        $this->assertEquals(1, $countWithNullReward);
    }

    public function testCountWithNullableUser(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $user = $this->createUserForTest();

        $award1 = $this->createAwardForTest(['user' => $user]);
        $award2 = $this->createAwardForTest(['user' => null]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $countWithUser = $this->repository->count(['user' => $user]);
        $countWithNullUser = $this->repository->count(['user' => null]);

        $this->assertEquals(1, $countWithUser);
        $this->assertEquals(1, $countWithNullUser);
    }

    public function testFindByWithAssociatedRecord(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest();
        $record2 = $this->createRecordForTest();
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $award1 = $this->createAwardForTest(['record' => $record1]);
        $award2 = $this->createAwardForTest(['record' => $record2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $results = $this->repository->findBy(['record' => $record1]);

        $this->assertCount(1, $results);
        $this->assertSame($record1, $results[0]->getRecord());
    }

    public function testFindByWithAssociatedReward(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest();
        $reward2 = $this->createRewardForTest();
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $award1 = $this->createAwardForTest(['reward' => $reward1]);
        $award2 = $this->createAwardForTest(['reward' => $reward2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $results = $this->repository->findBy(['reward' => $reward1]);

        $this->assertCount(1, $results);
        $this->assertSame($reward1, $results[0]->getReward());
    }

    public function testFindByWithAssociatedUser(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $user1 = $this->createUserForTest();
        $user2 = $this->createUserForTest();

        $award1 = $this->createAwardForTest(['user' => $user1]);
        $award2 = $this->createAwardForTest(['user' => $user2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);

        $results = $this->repository->findBy(['user' => $user1]);

        $this->assertCount(1, $results);
        $this->assertSame($user1, $results[0]->getUser());
    }

    public function testCountWithAssociatedRecord(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest();
        $record2 = $this->createRecordForTest();
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $award1 = $this->createAwardForTest(['record' => $record1]);
        $award2 = $this->createAwardForTest(['record' => $record1]);
        $award3 = $this->createAwardForTest(['record' => $record2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);
        $this->persistAndFlush($award3);

        $countWithRecord1 = $this->repository->count(['record' => $record1]);
        $countWithRecord2 = $this->repository->count(['record' => $record2]);

        $this->assertEquals(2, $countWithRecord1);
        $this->assertEquals(1, $countWithRecord2);
    }

    public function testCountWithAssociatedReward(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest();
        $reward2 = $this->createRewardForTest();
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $award1 = $this->createAwardForTest(['reward' => $reward1]);
        $award2 = $this->createAwardForTest(['reward' => $reward1]);
        $award3 = $this->createAwardForTest(['reward' => $reward2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);
        $this->persistAndFlush($award3);

        $countWithReward1 = $this->repository->count(['reward' => $reward1]);
        $countWithReward2 = $this->repository->count(['reward' => $reward2]);

        $this->assertEquals(2, $countWithReward1);
        $this->assertEquals(1, $countWithReward2);
    }

    public function testCountWithAssociatedUser(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $user1 = $this->createUserForTest();
        $user2 = $this->createUserForTest();

        $award1 = $this->createAwardForTest(['user' => $user1]);
        $award2 = $this->createAwardForTest(['user' => $user1]);
        $award3 = $this->createAwardForTest(['user' => $user2]);
        $this->persistAndFlush($award1);
        $this->persistAndFlush($award2);
        $this->persistAndFlush($award3);

        $countWithUser1 = $this->repository->count(['user' => $user1]);
        $countWithUser2 = $this->repository->count(['user' => $user2]);

        $this->assertEquals(2, $countWithUser1);
        $this->assertEquals(1, $countWithUser2);
    }

    public function testSaveWithFlush(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $award = $this->createAwardForTest();

        $this->repository->save($award, true);

        $this->assertNotNull($award->getId());
        $found = $this->repository->find($award->getId());
        $this->assertInstanceOf(Award::class, $found);
    }

    public function testSaveWithoutFlush(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $award = $this->createAwardForTest();

        $this->repository->save($award, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($award->getId());
        $found = $this->repository->find($award->getId());
        $this->assertInstanceOf(Award::class, $found);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createAwardForTest(array $overrides = []): Award
    {
        $award = new Award();
        $award->setCreateTime(new \DateTimeImmutable());
        $award->setUpdateTime(new \DateTimeImmutable());
        $award->setCreatedBy('test_user');
        $award->setUpdatedBy('test_user');

        if (isset($overrides['record'])) {
            $award->setRecord($this->extractRecord($overrides, 'record'));
        }
        if (isset($overrides['reward'])) {
            $award->setReward($this->extractReward($overrides, 'reward'));
        }
        if (isset($overrides['user'])) {
            $user = $this->extractUser($overrides, 'user');
            // 确保用户实体已持久化
            if ($user instanceof UserInterface && !self::getEntityManager()->contains($user)) {
                self::getEntityManager()->persist($user);
            }
            $award->setUser($user);
        }

        return $award;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractRecord(array $data, string $key): ?Record
    {
        if (!isset($data[$key])) {
            return null;
        }

        return $data[$key] instanceof Record ? $data[$key] : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractReward(array $data, string $key): ?Reward
    {
        if (!isset($data[$key])) {
            return null;
        }

        return $data[$key] instanceof Reward ? $data[$key] : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractUser(array $data, string $key): ?UserInterface
    {
        if (!isset($data[$key])) {
            return null;
        }

        return $data[$key] instanceof UserInterface ? $data[$key] : null;
    }

    private function createRecordForTest(): Record
    {
        $activity = $this->createActivityForTest();
        $user = $this->createUserForTest();
        $this->persistAndFlush($activity);
        $this->persistAndFlush($user);

        $record = new Record();
        $record->setActivity($activity);
        $record->setUser($user);
        $record->setCheckinDate(new \DateTimeImmutable());
        $record->setCheckinTimes(1);
        $record->setHasAward(false);
        $record->setCreateTime(new \DateTimeImmutable());
        $record->setUpdateTime(new \DateTimeImmutable());
        $record->setCreatedBy('test_user');
        $record->setUpdatedBy('test_user');

        return $record;
    }

    private function createRewardForTest(): Reward
    {
        $reward = new Reward();
        $reward->setName('Test Reward ' . uniqid());
        $reward->setType(RewardType::CREDIT);
        $reward->setValue('100');
        $reward->setTimes(1);
        $reward->setQuantity(10);
        $reward->setDayLimit(1);
        $reward->setIsDefault(false);
        $reward->setCanShowPrize(true);
        $reward->setSortNumber(0);
        $reward->setRewardGetType(RewardGetType::AND);
        $reward->setCreateTime(new \DateTimeImmutable());
        $reward->setUpdateTime(new \DateTimeImmutable());
        $reward->setCreatedBy('test_user');
        $reward->setUpdatedBy('test_user');

        return $reward;
    }

    private function createActivityForTest(): Activity
    {
        $activity = new Activity();
        $activity->setTitle('Test Activity ' . uniqid());
        $activity->setStartTime(new \DateTimeImmutable());
        $activity->setEndTime(new \DateTimeImmutable('+30 days'));
        $activity->setTimes(7);
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::CONTINUE);
        $activity->setCreateTime(new \DateTimeImmutable());
        $activity->setUpdateTime(new \DateTimeImmutable());
        $activity->setCreatedBy('test_user');
        $activity->setUpdatedBy('test_user');

        return $activity;
    }

    private function createUserForTest(): UserInterface
    {
        return $this->createNormalUser('test' . uniqid() . '@example.com', 'password123');
    }

    protected function createNewEntity(): Award
    {
        return $this->createAwardForTest();
    }

    protected function getRepository(): AwardRepository
    {
        return $this->repository;
    }
}
