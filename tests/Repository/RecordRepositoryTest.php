<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Repository\RecordRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template-extends AbstractRepositoryTestCase<Record>
 * @internal
 */
#[CoversClass(RecordRepository::class)]
#[RunTestsInSeparateProcesses]
final class RecordRepositoryTest extends AbstractRepositoryTestCase
{
    private RecordRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(RecordRepository::class);
    }

    public function testFindWithExistingId(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest();
        $this->persistAndFlush($record);

        $found = $this->repository->find($record->getId());

        $this->assertInstanceOf(Record::class, $found);
        $this->assertEquals($record->getId(), $found->getId());
        $this->assertEquals($record->getCheckinTimes(), $found->getCheckinTimes());
    }

    public function testFindWithNonExistentId(): void
    {
        $result = $this->repository->find('999999999999999999');

        $this->assertNull($result);
    }

    public function testFindAll(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest();
        $record2 = $this->createRecordForTest();
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $results = $this->repository->findAll();

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(Record::class, $result);
        }
    }

    public function testFindByWithActivity(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $record = $this->createRecordForTest(['activity' => $activity]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($record);

        $results = $this->repository->findBy(['activity' => $activity]);

        $this->assertCount(1, $results);
        $this->assertSame($activity, $results[0]->getActivity());
    }

    public function testFindByWithUser(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record = $this->createRecordForTest(['user' => $user]);
        $this->persistAndFlush($record);

        $results = $this->repository->findBy(['user' => $user]);

        $this->assertCount(1, $results);
        $this->assertSame($user, $results[0]->getUser());
    }

    public function testFindByWithCheckinDate(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $checkinDate = new \DateTimeImmutable('2025-01-15');
        $record = $this->createRecordForTest(['checkinDate' => $checkinDate]);
        $this->persistAndFlush($record);

        $results = $this->repository->findBy(['checkinDate' => $checkinDate]);

        $this->assertCount(1, $results);
        $this->assertEquals($checkinDate, $results[0]->getCheckinDate());
    }

    public function testFindByWithCheckinTimes(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest(['checkinTimes' => 5]);
        $record2 = $this->createRecordForTest(['checkinTimes' => 10]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $results = $this->repository->findBy(['checkinTimes' => 5]);

        $this->assertCount(1, $results);
        $this->assertSame(5, $results[0]->getCheckinTimes());
    }

    public function testFindByWithHasAward(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest(['hasAward' => true]);
        $record2 = $this->createRecordForTest(['hasAward' => false]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $resultsWithAward = $this->repository->findBy(['hasAward' => true]);
        $resultsWithoutAward = $this->repository->findBy(['hasAward' => false]);

        $this->assertCount(1, $resultsWithAward);
        $this->assertTrue($resultsWithAward[0]->hasAward());
        $this->assertCount(1, $resultsWithoutAward);
        $this->assertFalse($resultsWithoutAward[0]->hasAward());
    }

    public function testFindByWithRemark(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest(['remark' => 'Special remark']);
        $record2 = $this->createRecordForTest(['remark' => null]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $resultsWithRemark = $this->repository->findBy(['remark' => 'Special remark']);
        $resultsWithNullRemark = $this->repository->findBy(['remark' => null]);

        $this->assertCount(1, $resultsWithRemark);
        $this->assertSame('Special remark', $resultsWithRemark[0]->getRemark());
        $this->assertCount(1, $resultsWithNullRemark);
        $this->assertNull($resultsWithNullRemark[0]->getRemark());
    }

    public function testFindByWithNoMatch(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest(['checkinTimes' => 1]);
        $this->persistAndFlush($record);

        $results = $this->repository->findBy(['checkinTimes' => 999]);

        $this->assertCount(0, $results);
    }

    public function testFindByWithLimit(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        for ($i = 1; $i <= 5; ++$i) {
            $record = $this->createRecordForTest(['user' => $user]);
            $this->persistAndFlush($record);
        }

        $results = $this->repository->findBy(['user' => $user], null, 3);

        $this->assertCount(3, $results);
    }

    public function testFindByWithOffset(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        for ($i = 1; $i <= 5; ++$i) {
            $record = $this->createRecordForTest(['user' => $user]);
            $this->persistAndFlush($record);
        }

        $results = $this->repository->findBy(['user' => $user], null, 2, 2);

        $this->assertCount(2, $results);
    }

    public function testFindOneByWithExistingEntity(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record = $this->createRecordForTest(['user' => $user]);
        $this->persistAndFlush($record);

        $result = $this->repository->findOneBy(['user' => $user]);

        $this->assertInstanceOf(Record::class, $result);
        $this->assertSame($user, $result->getUser());
    }

    public function testFindOneByWithNonExistentEntity(): void
    {
        $user = $this->createUserForTest();

        $result = $this->repository->findOneBy(['user' => $user]);

        $this->assertNull($result);
    }

    public function testFindOneByWithMultipleResults(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record1 = $this->createRecordForTest(['user' => $user]);
        $record2 = $this->createRecordForTest(['user' => $user]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $result = $this->repository->findOneBy(['user' => $user]);

        $this->assertInstanceOf(Record::class, $result);
        $this->assertSame($user, $result->getUser());
    }

    public function testCount(): void
    {
        // 确保数据库清洁状态
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $this->assertSame(0, $this->repository->count([]));

        $record1 = $this->createRecordForTest();
        $record2 = $this->createRecordForTest();
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $this->assertSame(2, $this->repository->count([]));
    }

    public function testSaveWithFlush(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest();

        $this->repository->save($record, true);

        $this->assertEntityPersisted($record);
        $this->assertNotNull($record->getId());
    }

    public function testSaveWithoutFlush(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest();

        $this->repository->save($record, false);
        self::getEntityManager()->flush();

        $this->assertEntityPersisted($record);
        $this->assertNotNull($record->getId());
    }

    public function testFindByWithMultipleCriteria(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user1 = $this->createUserForTest();
        $user2 = $this->createUserForTest();
        $activity = $this->createActivityForTest();
        $record1 = $this->createRecordForTest(['user' => $user1, 'activity' => $activity, 'hasAward' => true]);
        $record2 = $this->createRecordForTest(['user' => $user2, 'activity' => $activity, 'hasAward' => false]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $results = $this->repository->findBy([
            'activity' => $activity,
            'hasAward' => true,
        ]);

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->hasAward());
    }

    public function testFindByWithSpecificActivity(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity1 = $this->createActivityForTest();
        $activity2 = $this->createActivityForTest();
        $user = $this->createUserForTest();

        $record1 = $this->createRecordForTest(['activity' => $activity1, 'user' => $user]);
        $record2 = $this->createRecordForTest(['activity' => $activity2, 'user' => $user, 'checkinDate' => new \DateTimeImmutable('+1 day')]);

        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);
        $this->persistAndFlush($user);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $resultsWithActivity1 = $this->repository->findBy(['activity' => $activity1]);
        $resultsWithActivity2 = $this->repository->findBy(['activity' => $activity2]);

        $this->assertCount(1, $resultsWithActivity1);
        $this->assertSame($activity1, $resultsWithActivity1[0]->getActivity());
        $this->assertCount(1, $resultsWithActivity2);
        $this->assertSame($activity2, $resultsWithActivity2[0]->getActivity());
    }

    public function testRepositoryInheritance(): void
    {
        $this->assertInstanceOf(
            ServiceEntityRepository::class,
            $this->repository
        );
    }

    public function testCountByAssociationActivityShouldReturnCorrectNumber(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity1 = $this->createActivityForTest();
        $activity2 = $this->createActivityForTest();
        $record1 = $this->createRecordForTest(['activity' => $activity1]);
        $record2 = $this->createRecordForTest(['activity' => $activity1]);
        $record3 = $this->createRecordForTest(['activity' => $activity1]);
        $record4 = $this->createRecordForTest(['activity' => $activity1]);
        $record5 = $this->createRecordForTest(['activity' => $activity2]);
        $record6 = $this->createRecordForTest(['activity' => $activity2]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);
        $this->persistAndFlush($record4);
        $this->persistAndFlush($record5);
        $this->persistAndFlush($record6);

        $count = $this->repository->count(['activity' => $activity1]);

        $this->assertSame(4, $count);
    }

    public function testCountByAssociationUserShouldReturnCorrectNumber(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user1 = $this->createUserForTest();
        $user2 = $this->createUserForTest();
        $record1 = $this->createRecordForTest(['user' => $user1]);
        $record2 = $this->createRecordForTest(['user' => $user1]);
        $record3 = $this->createRecordForTest(['user' => $user1]);
        $record4 = $this->createRecordForTest(['user' => $user2]);
        $record5 = $this->createRecordForTest(['user' => $user2]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);
        $this->persistAndFlush($record4);
        $this->persistAndFlush($record5);

        $count = $this->repository->count(['user' => $user1]);

        $this->assertSame(3, $count);
    }

    public function testFindOneByAssociationActivityShouldReturnMatchingEntity(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $record = $this->createRecordForTest(['activity' => $activity, 'checkinTimes' => 25]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($record);

        $result = $this->repository->findOneBy(['activity' => $activity]);

        $this->assertInstanceOf(Record::class, $result);
        $this->assertSame($activity, $result->getActivity());
        $this->assertSame(25, $result->getCheckinTimes());
    }

    public function testFindOneByAssociationUserShouldReturnMatchingEntity(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record = $this->createRecordForTest(['user' => $user, 'checkinTimes' => 12]);
        $this->persistAndFlush($record);

        $result = $this->repository->findOneBy(['user' => $user]);

        $this->assertInstanceOf(Record::class, $result);
        $this->assertSame($user, $result->getUser());
        $this->assertSame(12, $result->getCheckinTimes());
    }

    public function testCountWithMatchingCriteria(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record1 = $this->createRecordForTest(['user' => $user, 'hasAward' => true]);
        $record2 = $this->createRecordForTest(['user' => $user, 'hasAward' => false]);
        $record3 = $this->createRecordForTest(['hasAward' => true]); // different user
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);

        $countWithAward = $this->repository->count(['hasAward' => true]);
        $countForUser = $this->repository->count(['user' => $user]);
        $countUserWithAward = $this->repository->count(['user' => $user, 'hasAward' => true]);

        $this->assertSame(2, $countWithAward);
        $this->assertSame(2, $countForUser);
        $this->assertSame(1, $countUserWithAward);
    }

    public function testCountWithNonMatchingCriteria(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record = $this->createRecordForTest(['checkinTimes' => 5]);
        $this->persistAndFlush($record);

        $count = $this->repository->count(['checkinTimes' => 999]);

        $this->assertSame(0, $count);
    }

    public function testCountWithNullValues(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest(['remark' => 'Test remark']);
        $record2 = $this->createRecordForTest(['remark' => null]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $countWithRemark = $this->repository->count(['remark' => 'Test remark']);
        $countWithNullRemark = $this->repository->count(['remark' => null]);

        $this->assertSame(1, $countWithRemark);
        $this->assertSame(1, $countWithNullRemark);
    }

    public function testFindByWithDateRangeFiltering(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $date1 = new \DateTimeImmutable('2025-01-01');
        $date2 = new \DateTimeImmutable('2025-01-02');
        $date3 = new \DateTimeImmutable('2025-01-03');

        $record1 = $this->createRecordForTest(['checkinDate' => $date1]);
        $record2 = $this->createRecordForTest(['checkinDate' => $date2]);
        $record3 = $this->createRecordForTest(['checkinDate' => $date3]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);

        $resultsForDate2 = $this->repository->findBy(['checkinDate' => $date2]);

        $this->assertCount(1, $resultsForDate2);
        $this->assertEquals($date2, $resultsForDate2[0]->getCheckinDate());
    }

    public function testFindByWithComplexSorting(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $date1 = new \DateTimeImmutable('2025-01-01');
        $date2 = new \DateTimeImmutable('2025-01-02');

        $record1 = $this->createRecordForTest(['user' => $user, 'checkinDate' => $date2, 'checkinTimes' => 3]);
        $record2 = $this->createRecordForTest(['user' => $user, 'checkinDate' => $date1, 'checkinTimes' => 5]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        // Sort by checkinDate DESC, checkinTimes ASC
        $results = $this->repository->findBy(
            ['user' => $user],
            ['checkinDate' => 'DESC', 'checkinTimes' => 'ASC']
        );

        $this->assertCount(2, $results);
        $this->assertEquals($date2, $results[0]->getCheckinDate());
        $this->assertSame(3, $results[0]->getCheckinTimes());
        $this->assertEquals($date1, $results[1]->getCheckinDate());
        $this->assertSame(5, $results[1]->getCheckinTimes());
    }

    public function testFindByWithPaginationEdgeCases(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        for ($i = 1; $i <= 10; ++$i) {
            $record = $this->createRecordForTest(['user' => $user, 'checkinTimes' => $i]);
            $this->persistAndFlush($record);
        }

        // Test limit exceeding total count
        $results = $this->repository->findBy(['user' => $user], null, 20);
        $this->assertCount(10, $results);

        // Test offset near end
        $results = $this->repository->findBy(['user' => $user], null, 5, 8);
        $this->assertCount(2, $results);

        // Test offset exceeding total count
        $results = $this->repository->findBy(['user' => $user], null, 5, 15);
        $this->assertCount(0, $results);
    }

    public function testFindByWithNullActivityFilter(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $record1 = $this->createRecordForTest();
        $record1->setActivity(null);
        $record2 = $this->createRecordForTest(); // has activity
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $resultsWithNullActivity = $this->repository->findBy(['activity' => null]);

        $this->assertCount(1, $resultsWithNullActivity);
        $this->assertNull($resultsWithNullActivity[0]->getActivity());
    }

    public function testFindByWithMixedCriteriaTypes(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $activity = $this->createActivityForTest();
        $checkinDate = new \DateTimeImmutable('2025-01-15');

        $record1 = $this->createRecordForTest([
            'user' => $user,
            'activity' => $activity,
            'checkinDate' => $checkinDate,
            'checkinTimes' => 7,
            'hasAward' => true,
            'remark' => 'Test remark',
        ]);
        $record2 = $this->createRecordForTest([
            'user' => $user,
            'activity' => $activity,
            'checkinDate' => new \DateTimeImmutable('2025-01-16'),
            'checkinTimes' => 3,
            'hasAward' => false,
            'remark' => null,
        ]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $results = $this->repository->findBy([
            'user' => $user,
            'activity' => $activity,
            'hasAward' => true,
        ]);

        $this->assertCount(1, $results);
        $this->assertSame(7, $results[0]->getCheckinTimes());
        $this->assertTrue($results[0]->hasAward());
        $this->assertSame('Test remark', $results[0]->getRemark());
    }

    public function testFindOneByWithOrderByPriority(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $user = $this->createUserForTest();
        $record1 = $this->createRecordForTest(['user' => $user, 'checkinTimes' => 1]);
        $record2 = $this->createRecordForTest(['user' => $user, 'checkinTimes' => 10]);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        // Should return the one with highest checkinTimes due to ORDER BY
        $result = $this->repository->findOneBy(['user' => $user], ['checkinTimes' => 'DESC']);

        $this->assertInstanceOf(Record::class, $result);
        $this->assertSame(10, $result->getCheckinTimes());
    }

    public function testEntityFieldsAndRelations(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $user = $this->createUserForTest();
        $checkinDate = new \DateTimeImmutable('2025-01-20');

        $record = $this->createRecordForTest([
            'activity' => $activity,
            'user' => $user,
            'checkinDate' => $checkinDate,
            'checkinTimes' => 15,
            'hasAward' => true,
            'remark' => 'Integration test remark',
        ]);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($record);

        $found = $this->repository->find($record->getId());

        $this->assertInstanceOf(Record::class, $found);
        $this->assertSame($activity, $found->getActivity());
        $this->assertSame($user, $found->getUser());
        $this->assertEquals($checkinDate, $found->getCheckinDate());
        $this->assertSame(15, $found->getCheckinTimes());
        $this->assertTrue($found->hasAward());
        $this->assertSame('Integration test remark', $found->getRemark());
        $this->assertInstanceOf(Collection::class, $found->getAwards());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createRecordForTest(array $overrides = []): Record
    {
        $record = new Record();

        $activity = $this->extractActivity($overrides, 'activity') ?? $this->createActivityForTest();
        $user = $this->extractUser($overrides, 'user') ?? $this->createUserForTest();

        // 持久化关联实体
        if (!self::getEntityManager()->contains($activity)) {
            self::getEntityManager()->persist($activity);
        }
        if (!self::getEntityManager()->contains($user)) {
            self::getEntityManager()->persist($user);
        }

        $record->setActivity($activity);
        $record->setUser($user);

        $checkinDate = $this->extractCheckinDate($overrides, 'checkinDate');
        $record->setCheckinDate($checkinDate);

        $record->setCheckinTimes($this->extractInt($overrides, 'checkinTimes', 1));

        $record->setHasAward($this->extractBool($overrides, 'hasAward', false));

        if (array_key_exists('remark', $overrides)) {
            $record->setRemark($this->extractNullableString($overrides, 'remark'));
        }

        return $record;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractActivity(array $data, string $key): ?Activity
    {
        if (!isset($data[$key])) {
            return null;
        }

        return $data[$key] instanceof Activity ? $data[$key] : null;
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

    /**
     * @param array<string, mixed> $data
     */
    private function extractCheckinDate(array $data, string $key): \DateTimeInterface
    {
        if (!isset($data[$key])) {
            return new \DateTimeImmutable();
        }

        return $data[$key] instanceof \DateTimeInterface ? $data[$key] : new \DateTimeImmutable();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractInt(array $data, string $key, int $default): int
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return \is_int($data[$key]) ? $data[$key] : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractBool(array $data, string $key, bool $default): bool
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return \is_bool($data[$key]) ? $data[$key] : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractNullableString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];

        return \is_string($value) || null === $value ? $value : null;
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

    protected function createNewEntity(): Record
    {
        return $this->createRecordForTest();
    }

    public function testFindByActivityAndUserWithJoins(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $activity = $this->createActivityForTest();
        $user = $this->createUserForTest();

        // 创建不同日期的记录以避免唯一约束冲突
        $date1 = new \DateTimeImmutable('2025-01-01');
        $date2 = new \DateTimeImmutable('2025-01-02');
        $date3 = new \DateTimeImmutable('2025-01-03');

        $record1 = $this->createRecordForTest(['activity' => $activity, 'user' => $user, 'checkinTimes' => 5, 'checkinDate' => $date1]);
        $record2 = $this->createRecordForTest(['activity' => $activity, 'user' => $user, 'checkinTimes' => 3, 'checkinDate' => $date2]);
        $record3 = $this->createRecordForTest(['activity' => $activity, 'user' => $this->createUserForTest(), 'checkinDate' => $date3]);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);

        $results = $this->repository->findByActivityAndUserWithJoins($activity, $user);

        $this->assertCount(2, $results);

        // 验证结果是按 ID 降序排列的
        $this->assertGreaterThan($results[1]->getId(), $results[0]->getId());

        // 验证关联实体已正确加载
        foreach ($results as $result) {
            $this->assertSame($activity, $result->getActivity());
            $this->assertSame($user, $result->getUser());
            $this->assertInstanceOf(Collection::class, $result->getAwards());
        }

        // 验证具体的记录值
        $checkinTimes = array_map(fn($record) => $record->getCheckinTimes(), $results);
        $this->assertContains(5, $checkinTimes);
        $this->assertContains(3, $checkinTimes);
    }

    public function testFindRecentRecordsWithJoins(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Award')->execute();

        $activity = $this->createActivityForTest();
        $user1 = $this->createUserForTest();
        $user2 = $this->createUserForTest();

        // 创建不同日期的记录以避免唯一约束冲突
        $dates = [
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-01-02'),
            new \DateTimeImmutable('2025-01-03'),
            new \DateTimeImmutable('2025-01-04'),
            new \DateTimeImmutable('2025-01-05'),
        ];

        // 创建多个记录，测试排序和限制
        $record1 = $this->createRecordForTest(['activity' => $activity, 'user' => $user1, 'checkinTimes' => 1, 'checkinDate' => $dates[0]]);
        $record2 = $this->createRecordForTest(['activity' => $activity, 'user' => $user2, 'checkinTimes' => 2, 'checkinDate' => $dates[1]]);
        $record3 = $this->createRecordForTest(['activity' => $activity, 'user' => $user1, 'checkinTimes' => 3, 'checkinDate' => $dates[2]]);
        $record4 = $this->createRecordForTest(['activity' => $activity, 'user' => $user2, 'checkinTimes' => 4, 'checkinDate' => $dates[3]]);
        $record5 = $this->createRecordForTest(['activity' => $activity, 'user' => $user1, 'checkinTimes' => 5, 'checkinDate' => $dates[4]]);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);
        $this->persistAndFlush($record4);
        $this->persistAndFlush($record5);

        // 测试默认限制（10条）
        $results = $this->repository->findRecentRecordsWithJoins($activity);
        $this->assertCount(5, $results);

        // 测试自定义限制（3条）
        $limitedResults = $this->repository->findRecentRecordsWithJoins($activity, 3);
        $this->assertCount(3, $limitedResults);

        // 验证结果是按 ID 降序排列的
        for ($i = 0; $i < count($limitedResults) - 1; $i++) {
            $this->assertGreaterThan($limitedResults[$i + 1]->getId(), $limitedResults[$i]->getId());
        }

        // 验证关联实体已正确加载
        foreach ($limitedResults as $result) {
            $this->assertSame($activity, $result->getActivity());
            $this->assertNotNull($result->getUser());
            $this->assertInstanceOf(Collection::class, $result->getAwards());
        }

        // 验证获取的是最近的记录（ID最大的）
        $expectedCheckinTimes = [5, 4, 3]; // 按创建顺序，后面的ID更大
        $actualCheckinTimes = array_map(fn($record) => $record->getCheckinTimes(), $limitedResults);
        $this->assertEquals($expectedCheckinTimes, $actualCheckinTimes);
    }

    public function testFindRecentRecordsWithJoinsWithEmptyResult(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $this->persistAndFlush($activity);

        $results = $this->repository->findRecentRecordsWithJoins($activity);

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    public function testFindByActivityAndUserWithJoinsWithEmptyResult(): void
    {
        // 清理数据库以确保测试独立性
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Record')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $user = $this->createUserForTest();
        $this->persistAndFlush($activity);

        // 不持久化任何记录

        $results = $this->repository->findByActivityAndUserWithJoins($activity, $user);

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    protected function getRepository(): RecordRepository
    {
        return $this->repository;
    }
}
