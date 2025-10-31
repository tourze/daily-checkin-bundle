<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Repository\RewardRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template-extends AbstractRepositoryTestCase<Reward>
 * @internal
 */
#[CoversClass(RewardRepository::class)]
#[RunTestsInSeparateProcesses]
final class RewardRepositoryTest extends AbstractRepositoryTestCase
{
    private RewardRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(RewardRepository::class);
    }

    public function testFindWithNonExistentId(): void
    {
        $result = $this->repository->find('999999999999999999');

        $this->assertNull($result);
    }

    public function testFindByWithType(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['type' => RewardType::CREDIT]);
        $reward2 = $this->createRewardForTest(['type' => RewardType::COUPON]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $creditResults = $this->repository->findBy(['type' => RewardType::CREDIT]);
        $couponResults = $this->repository->findBy(['type' => RewardType::COUPON]);

        $this->assertCount(1, $creditResults);
        $this->assertSame(RewardType::CREDIT, $creditResults[0]->getType());
        $this->assertCount(1, $couponResults);
        $this->assertSame(RewardType::COUPON, $couponResults[0]->getType());
    }

    public function testFindByWithTimes(): void
    {
        $reward1 = $this->createRewardForTest(['times' => 1]);
        $reward2 = $this->createRewardForTest(['times' => 7]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $results = $this->repository->findBy(['times' => 7]);

        $this->assertCount(1, $results);
        $this->assertSame(7, $results[0]->getTimes());
    }

    public function testFindByWithActivity(): void
    {
        $activity = $this->createActivityForTest();
        $reward = $this->createRewardForTest(['activity' => $activity]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);

        $results = $this->repository->findBy(['activity' => $activity]);

        $this->assertCount(1, $results);
        $this->assertSame($activity, $results[0]->getActivity());
    }

    public function testFindByWithQuantity(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['quantity' => 100]);
        $reward2 = $this->createRewardForTest(['quantity' => 50]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $results = $this->repository->findBy(['quantity' => 100]);

        $this->assertCount(1, $results);
        $this->assertSame(100, $results[0]->getQuantity());
    }

    public function testFindByWithDayLimit(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['dayLimit' => 1]);
        $reward2 = $this->createRewardForTest(['dayLimit' => 5]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $results = $this->repository->findBy(['dayLimit' => 1]);

        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]->getDayLimit());
    }

    public function testFindByWithIsDefault(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['isDefault' => true]);
        $reward2 = $this->createRewardForTest(['isDefault' => false]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $defaultResults = $this->repository->findBy(['isDefault' => true]);
        $nonDefaultResults = $this->repository->findBy(['isDefault' => false]);

        $this->assertCount(1, $defaultResults);
        $this->assertTrue($defaultResults[0]->getIsDefault());
        $this->assertCount(1, $nonDefaultResults);
        $this->assertFalse($nonDefaultResults[0]->getIsDefault());
    }

    public function testFindByWithCanShowPrize(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['canShowPrize' => true]);
        $reward2 = $this->createRewardForTest(['canShowPrize' => false]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $showResults = $this->repository->findBy(['canShowPrize' => true]);
        $hideResults = $this->repository->findBy(['canShowPrize' => false]);

        $this->assertCount(1, $showResults);
        $this->assertTrue($showResults[0]->getCanShowPrize());
        $this->assertCount(1, $hideResults);
        $this->assertFalse($hideResults[0]->getCanShowPrize());
    }

    public function testFindByWithRewardGetType(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['rewardGetType' => RewardGetType::AND]);
        $reward2 = $this->createRewardForTest(['rewardGetType' => RewardGetType::OR]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $andResults = $this->repository->findBy(['rewardGetType' => RewardGetType::AND]);
        $orResults = $this->repository->findBy(['rewardGetType' => RewardGetType::OR]);

        $this->assertCount(1, $andResults);
        $this->assertSame(RewardGetType::AND, $andResults[0]->getRewardGetType());
        $this->assertCount(1, $orResults);
        $this->assertSame(RewardGetType::OR, $orResults[0]->getRewardGetType());
    }

    public function testFindByWithNoMatch(): void
    {
        $reward = $this->createRewardForTest(['name' => 'Test Reward']);
        $this->persistAndFlush($reward);

        $results = $this->repository->findBy(['name' => 'Non-existent Reward']);

        $this->assertCount(0, $results);
    }

    public function testFindByWithLimit(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $this->persistAndFlush($activity);

        for ($i = 1; $i <= 5; ++$i) {
            $reward = $this->createRewardForTest(['activity' => $activity, 'times' => $i]);
            $this->persistAndFlush($reward);
        }

        $results = $this->repository->findBy(['activity' => $activity], null, 3);

        $this->assertCount(3, $results);
    }

    public function testFindByWithOffset(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $this->persistAndFlush($activity);

        for ($i = 1; $i <= 5; ++$i) {
            $reward = $this->createRewardForTest(['activity' => $activity, 'times' => $i]);
            $this->persistAndFlush($reward);
        }

        $results = $this->repository->findBy(['activity' => $activity], null, 2, 2);

        $this->assertCount(2, $results);
    }

    public function testFindByWithSortNumber(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['sortNumber' => 10]);
        $reward2 = $this->createRewardForTest(['sortNumber' => 5]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $results = $this->repository->findBy([], ['sortNumber' => 'ASC']);

        $this->assertCount(2, $results);
        $this->assertSame(5, $results[0]->getSortNumber());
        $this->assertSame(10, $results[1]->getSortNumber());
    }

    public function testFindOneByWithNonExistentEntity(): void
    {
        $result = $this->repository->findOneBy(['name' => 'Non-existent Reward']);

        $this->assertNull($result);
    }

    public function testFindOneByWithMultipleResults(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['times' => 7]);
        $reward2 = $this->createRewardForTest(['times' => 7]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $result = $this->repository->findOneBy(['times' => 7]);

        $this->assertInstanceOf(Reward::class, $result);
        $this->assertSame(7, $result->getTimes());
    }

    public function testSaveWithFlush(): void
    {
        $reward = $this->createRewardForTest();

        $this->repository->save($reward, true);

        $this->assertEntityPersisted($reward);
        $this->assertNotNull($reward->getId());
        $this->assertInstanceOf(Reward::class, $this->repository->find($reward->getId()));
    }

    public function testSaveWithoutFlush(): void
    {
        $reward = $this->createRewardForTest();

        $this->repository->save($reward, false);
        self::getEntityManager()->flush();

        $this->assertEntityPersisted($reward);
        $this->assertNotNull($reward->getId());
        $this->assertInstanceOf(Reward::class, $this->repository->find($reward->getId()));
    }

    public function testFindByWithNullableFields(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforePicture' => 'test.jpg']);
        $reward2 = $this->createRewardForTest(['beforePicture' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithPicture = $this->repository->findBy(['beforePicture' => 'test.jpg']);
        $resultsWithNullPicture = $this->repository->findBy(['beforePicture' => null]);

        $this->assertCount(1, $resultsWithPicture);
        $this->assertSame('test.jpg', $resultsWithPicture[0]->getBeforePicture());
        $this->assertCount(1, $resultsWithNullPicture);
        $this->assertNull($resultsWithNullPicture[0]->getBeforePicture());
    }

    public function testFindByWithArrayFields(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $afterPicture = ['pic1.jpg', 'pic2.jpg'];
        $otherPicture = ['other1.jpg', 'other2.jpg'];

        $reward1 = $this->createRewardForTest(['afterPicture' => $afterPicture]);
        $reward2 = $this->createRewardForTest(['otherPicture' => $otherPicture]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        // 由于数组字段在数据库中以JSON形式存储，不能直接通过数组值查询
        // 因此我们测试通过ID查找，然后验证数组字段值
        $foundReward1 = $this->repository->find($reward1->getId());
        $foundReward2 = $this->repository->find($reward2->getId());

        $this->assertNotNull($foundReward1);
        $this->assertSame($afterPicture, $foundReward1->getAfterPicture());
        $this->assertNotNull($foundReward2);
        $this->assertSame($otherPicture, $foundReward2->getOtherPicture());
    }

    public function testFindByWithNullActivity(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $reward1 = $this->createRewardForTest(['activity' => null]);
        $activity = $this->createActivityForTest();
        $reward2 = $this->createRewardForTest(['activity' => $activity]);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithNullActivity = $this->repository->findBy(['activity' => null]);
        $resultsWithActivity = $this->repository->findBy(['activity' => $activity]);

        $this->assertCount(1, $resultsWithNullActivity);
        $this->assertNull($resultsWithNullActivity[0]->getActivity());
        $this->assertCount(1, $resultsWithActivity);
        $this->assertSame($activity, $resultsWithActivity[0]->getActivity());
    }

    public function testFindByWithMultipleConditions(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest([
            'name' => 'Multi Test Reward',
            'type' => RewardType::CREDIT,
            'times' => 7,
            'isDefault' => true,
        ]);
        $reward2 = $this->createRewardForTest([
            'name' => 'Multi Test Reward',
            'type' => RewardType::COUPON,
            'times' => 7,
            'isDefault' => false,
        ]);
        $reward3 = $this->createRewardForTest([
            'name' => 'Other Reward',
            'type' => RewardType::CREDIT,
            'times' => 7,
            'isDefault' => true,
        ]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);
        $this->persistAndFlush($reward3);

        $results = $this->repository->findBy([
            'name' => 'Multi Test Reward',
            'type' => RewardType::CREDIT,
            'times' => 7,
            'isDefault' => true,
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('Multi Test Reward', $results[0]->getName());
        $this->assertSame(RewardType::CREDIT, $results[0]->getType());
        $this->assertSame(7, $results[0]->getTimes());
        $this->assertTrue($results[0]->getIsDefault());
    }

    public function testFindByWithComplexOrderBy(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['times' => 7, 'sortNumber' => 10, 'name' => 'Z Reward']);
        $reward2 = $this->createRewardForTest(['times' => 1, 'sortNumber' => 5, 'name' => 'A Reward']);
        $reward3 = $this->createRewardForTest(['times' => 7, 'sortNumber' => 5, 'name' => 'B Reward']);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);
        $this->persistAndFlush($reward3);

        // 按 times DESC, sortNumber ASC, name ASC 排序
        $results = $this->repository->findBy([], [
            'times' => 'DESC',
            'sortNumber' => 'ASC',
            'name' => 'ASC',
        ]);

        $this->assertCount(3, $results);
        $this->assertSame('B Reward', $results[0]->getName()); // times=7, sortNumber=5
        $this->assertSame('Z Reward', $results[1]->getName()); // times=7, sortNumber=10
        $this->assertSame('A Reward', $results[2]->getName()); // times=1, sortNumber=5
    }

    public function testFindByWithZeroLimit(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest();
        $reward2 = $this->createRewardForTest();
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        // limit为0应该返回空数组
        $results = $this->repository->findBy([], null, 0);

        $this->assertCount(0, $results);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['times' => 7, 'name' => 'Second']);
        $reward2 = $this->createRewardForTest(['times' => 1, 'name' => 'First']);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        // 应该返回按name排序的第一个
        $result = $this->repository->findOneBy([], ['name' => 'ASC']);

        $this->assertInstanceOf(Reward::class, $result);
        $this->assertSame('First', $result->getName());
    }

    public function testCountWithComplexConditions(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity = $this->createActivityForTest();
        $this->persistAndFlush($activity);

        $reward1 = $this->createRewardForTest([
            'activity' => $activity,
            'type' => RewardType::CREDIT,
            'isDefault' => true,
            'canShowPrize' => true,
        ]);
        $reward2 = $this->createRewardForTest([
            'activity' => $activity,
            'type' => RewardType::COUPON,
            'isDefault' => false,
            'canShowPrize' => true,
        ]);
        $reward3 = $this->createRewardForTest([
            'activity' => null,
            'type' => RewardType::CREDIT,
            'isDefault' => true,
            'canShowPrize' => false,
        ]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);
        $this->persistAndFlush($reward3);

        $this->assertSame(2, $this->repository->count(['activity' => $activity]));
        $this->assertSame(2, $this->repository->count(['type' => RewardType::CREDIT]));
        $this->assertSame(2, $this->repository->count(['isDefault' => true]));
        $this->assertSame(2, $this->repository->count(['canShowPrize' => true]));
        $this->assertSame(1, $this->repository->count([
            'activity' => $activity,
            'type' => RewardType::CREDIT,
            'isDefault' => true,
        ]));
    }

    public function testFindByWithNullValueQueries(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforePicture' => null, 'afterButton' => null]);
        $reward2 = $this->createRewardForTest(['beforePicture' => 'pic.jpg', 'afterButton' => 'button.png']);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        // 测试查询NULL值
        $nullResults = $this->repository->findBy(['beforePicture' => null]);
        $nonNullResults = $this->repository->findBy(['beforePicture' => 'pic.jpg']);

        $this->assertCount(1, $nullResults);
        $this->assertNull($nullResults[0]->getBeforePicture());
        $this->assertCount(1, $nonNullResults);
        $this->assertSame('pic.jpg', $nonNullResults[0]->getBeforePicture());
    }

    public function testRepositoryInheritance(): void
    {
        $this->assertInstanceOf(
            ServiceEntityRepository::class,
            $this->repository
        );
    }

    public function testFindByWithAssociatedActivity(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity1 = $this->createActivityForTest();
        $activity2 = $this->createActivityForTest();
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $reward1 = $this->createRewardForTest(['activity' => $activity1]);
        $reward2 = $this->createRewardForTest(['activity' => $activity2]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $results = $this->repository->findBy(['activity' => $activity1]);

        $this->assertCount(1, $results);
        $this->assertSame($activity1, $results[0]->getActivity());
    }

    public function testCountWithAssociatedActivity(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Activity')->execute();

        $activity1 = $this->createActivityForTest();
        $activity2 = $this->createActivityForTest();
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $reward1 = $this->createRewardForTest(['activity' => $activity1]);
        $reward2 = $this->createRewardForTest(['activity' => $activity1]);
        $reward3 = $this->createRewardForTest(['activity' => $activity2]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);
        $this->persistAndFlush($reward3);

        $countWithActivity1 = $this->repository->count(['activity' => $activity1]);
        $countWithActivity2 = $this->repository->count(['activity' => $activity2]);

        $this->assertEquals(2, $countWithActivity1);
        $this->assertEquals(1, $countWithActivity2);
    }

    public function testFindByWithNullableBeforePicture(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforePicture' => 'test.jpg']);
        $reward2 = $this->createRewardForTest(['beforePicture' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithPicture = $this->repository->findBy(['beforePicture' => 'test.jpg']);
        $resultsWithNullPicture = $this->repository->findBy(['beforePicture' => null]);

        $this->assertCount(1, $resultsWithPicture);
        $this->assertEquals('test.jpg', $resultsWithPicture[0]->getBeforePicture());
        $this->assertCount(1, $resultsWithNullPicture);
        $this->assertNull($resultsWithNullPicture[0]->getBeforePicture());
    }

    public function testCountWithNullableBeforePicture(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforePicture' => 'test.jpg']);
        $reward2 = $this->createRewardForTest(['beforePicture' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $countWithPicture = $this->repository->count(['beforePicture' => 'test.jpg']);
        $countWithNullPicture = $this->repository->count(['beforePicture' => null]);

        $this->assertEquals(1, $countWithPicture);
        $this->assertEquals(1, $countWithNullPicture);
    }

    public function testFindByWithNullableRemark(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['remark' => 'Test remark']);
        $reward2 = $this->createRewardForTest(['remark' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithRemark = $this->repository->findBy(['remark' => 'Test remark']);
        $resultsWithNullRemark = $this->repository->findBy(['remark' => null]);

        $this->assertCount(1, $resultsWithRemark);
        $this->assertEquals('Test remark', $resultsWithRemark[0]->getRemark());
        $this->assertCount(1, $resultsWithNullRemark);
        $this->assertNull($resultsWithNullRemark[0]->getRemark());
    }

    public function testCountWithNullableRemark(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['remark' => 'Test remark']);
        $reward2 = $this->createRewardForTest(['remark' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $countWithRemark = $this->repository->count(['remark' => 'Test remark']);
        $countWithNullRemark = $this->repository->count(['remark' => null]);

        $this->assertEquals(1, $countWithRemark);
        $this->assertEquals(1, $countWithNullRemark);
    }

    public function testFindByWithNullableBeforeButton(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforeButton' => 'button.png']);
        $reward2 = $this->createRewardForTest(['beforeButton' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithButton = $this->repository->findBy(['beforeButton' => 'button.png']);
        $resultsWithNullButton = $this->repository->findBy(['beforeButton' => null]);

        $this->assertCount(1, $resultsWithButton);
        $this->assertEquals('button.png', $resultsWithButton[0]->getBeforeButton());
        $this->assertCount(1, $resultsWithNullButton);
        $this->assertNull($resultsWithNullButton[0]->getBeforeButton());
    }

    public function testCountWithNullableBeforeButton(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['beforeButton' => 'button.png']);
        $reward2 = $this->createRewardForTest(['beforeButton' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $countWithButton = $this->repository->count(['beforeButton' => 'button.png']);
        $countWithNullButton = $this->repository->count(['beforeButton' => null]);

        $this->assertEquals(1, $countWithButton);
        $this->assertEquals(1, $countWithNullButton);
    }

    public function testFindByWithNullableAfterButton(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['afterButton' => 'button.png']);
        $reward2 = $this->createRewardForTest(['afterButton' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $resultsWithButton = $this->repository->findBy(['afterButton' => 'button.png']);
        $resultsWithNullButton = $this->repository->findBy(['afterButton' => null]);

        $this->assertCount(1, $resultsWithButton);
        $this->assertEquals('button.png', $resultsWithButton[0]->getAfterButton());
        $this->assertCount(1, $resultsWithNullButton);
        $this->assertNull($resultsWithNullButton[0]->getAfterButton());
    }

    public function testCountWithNullableAfterButton(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['afterButton' => 'button.png']);
        $reward2 = $this->createRewardForTest(['afterButton' => null]);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $countWithButton = $this->repository->count(['afterButton' => 'button.png']);
        $countWithNullButton = $this->repository->count(['afterButton' => null]);

        $this->assertEquals(1, $countWithButton);
        $this->assertEquals(1, $countWithNullButton);
    }

    public function testFindOneByTimesOrderBy(): void
    {
        // 清理数据库确保测试环境干净
        self::getEntityManager()->createQuery('DELETE FROM DailyCheckinBundle\Entity\Reward')->execute();

        $reward1 = $this->createRewardForTest(['times' => 7, 'name' => 'High Times']);
        $reward2 = $this->createRewardForTest(['times' => 1, 'name' => 'Low Times']);
        $this->persistAndFlush($reward1);
        $this->persistAndFlush($reward2);

        $result = $this->repository->findOneBy([], ['times' => 'DESC']);

        $this->assertInstanceOf(Reward::class, $result);
        $this->assertEquals(7, $result->getTimes());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createRewardForTest(array $overrides = []): Reward
    {
        $reward = new Reward();

        $reward->setName($this->extractString($overrides, 'name', 'Test Reward ' . uniqid()));
        $reward->setType($this->extractRewardType($overrides, 'type', RewardType::CREDIT));
        $reward->setValue($this->extractString($overrides, 'value', '100'));
        $reward->setTimes($this->extractInt($overrides, 'times', 1));
        $reward->setQuantity($this->extractInt($overrides, 'quantity', 10));
        $reward->setDayLimit($this->extractInt($overrides, 'dayLimit', 1));
        $reward->setIsDefault($this->extractBool($overrides, 'isDefault', false));
        $reward->setCanShowPrize($this->extractBool($overrides, 'canShowPrize', true));
        $reward->setSortNumber($this->extractInt($overrides, 'sortNumber', 0));
        $reward->setRewardGetType($this->extractRewardGetType($overrides, 'rewardGetType', RewardGetType::AND));

        if (isset($overrides['activity'])) {
            $reward->setActivity($this->extractActivity($overrides, 'activity'));
        }
        if (array_key_exists('beforePicture', $overrides)) {
            $reward->setBeforePicture($this->extractNullableString($overrides, 'beforePicture'));
        }
        if (array_key_exists('afterPicture', $overrides)) {
            $reward->setAfterPicture($this->extractNullableArray($overrides, 'afterPicture'));
        }
        if (array_key_exists('beforeButton', $overrides)) {
            $reward->setBeforeButton($this->extractNullableString($overrides, 'beforeButton'));
        }
        if (array_key_exists('afterButton', $overrides)) {
            $reward->setAfterButton($this->extractNullableString($overrides, 'afterButton'));
        }
        if (array_key_exists('remark', $overrides)) {
            $reward->setRemark($this->extractNullableString($overrides, 'remark'));
        }
        if (array_key_exists('otherPicture', $overrides)) {
            $reward->setOtherPicture($this->extractNullableArray($overrides, 'otherPicture'));
        }

        return $reward;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractString(array $data, string $key, string $default): string
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return \is_string($data[$key]) ? $data[$key] : $default;
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

        return \is_string($value) ? $value : null;
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
     * @return array<mixed>|null
     */
    private function extractNullableArray(array $data, string $key): ?array
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];

        return \is_array($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractRewardType(array $data, string $key, RewardType $default): RewardType
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return $data[$key] instanceof RewardType ? $data[$key] : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractRewardGetType(array $data, string $key, RewardGetType $default): RewardGetType
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return $data[$key] instanceof RewardGetType ? $data[$key] : $default;
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

    private function createActivityForTest(): Activity
    {
        $activity = new Activity();
        $activity->setTitle('Test Activity ' . uniqid());
        $activity->setStartTime(new \DateTimeImmutable());
        $activity->setEndTime(new \DateTimeImmutable('+30 days'));
        $activity->setTimes(7);
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::CONTINUE);

        return $activity;
    }

    protected function createNewEntity(): Reward
    {
        return $this->createRewardForTest();
    }

    protected function getRepository(): RewardRepository
    {
        return $this->repository;
    }
}
