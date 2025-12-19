<?php

namespace DailyCheckinBundle\Tests\Procedure;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Procedure\GetUserCheckinRecords;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserCheckinRecords::class)]
#[RunTestsInSeparateProcesses]
final class GetUserCheckinRecordsTest extends AbstractProcedureTestCase
{
    private GetUserCheckinRecords $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetUserCheckinRecords::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetUserCheckinRecords::class, $this->procedure);
    }

    public function testExecuteWithValidActivity(): void
    {
        $user = $this->createNormalUser();

        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('data', $result);
    }

    public function testExecuteWithNonExistentActivity(): void
    {
        $user = $this->createNormalUser();

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam('non-existent-id');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('暂无活动');

        $this->procedure->execute($param);
    }

    public function testExecuteWithNoRecords(): void
    {
        $user = $this->createNormalUser();

        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('data', $result);
        $this->assertEmpty($result['data']);
    }

    public function testExecuteWithRecordsAndAwards(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity();
        $reward = $this->createReward($activity);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);

        $record = $this->createRecord($activity, $user, hasAward: true);
        $this->persistAndFlush($record);

        $award = $this->createAward($record, $reward);
        $this->persistAndFlush($award);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
        $this->assertIsArray($result['data']);
        $firstData = $result['data'][0] ?? null;
        $this->assertIsArray($firstData);
        $this->assertArrayHasKey('choseReward', $firstData);
        $this->assertFalse($firstData['choseReward']);
    }

    public function testExecuteWithRecordsButNoChosenReward(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity();
        $reward = $this->createReward($activity);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);

        $record = $this->createRecord($activity, $user, hasAward: true, checkinTimes: 1);
        $this->persistAndFlush($record);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
        $this->assertIsArray($result['data']);
        $firstData = $result['data'][0] ?? null;
        $this->assertIsArray($firstData);
        $this->assertArrayHasKey('choseReward', $firstData);
        $this->assertTrue($firstData['choseReward']);
        $this->assertArrayHasKey('orPrizes', $firstData);
    }

    public function testExecuteWithMultipleRecords(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $record1 = $this->createRecord($activity, $user, hasAward: false, checkinTimes: 1, checkinDate: CarbonImmutable::now()->subDay());
        $record2 = $this->createRecord($activity, $user, hasAward: true, checkinTimes: 2, checkinDate: CarbonImmutable::now());
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetUserCheckinRecordsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertCount(2, $result['data']);

        $firstData = $result['data'][0] ?? null;
        $secondData = $result['data'][1] ?? null;
        $this->assertIsArray($firstData);
        $this->assertIsArray($secondData);
        $this->assertArrayHasKey('choseReward', $firstData);
        $this->assertArrayHasKey('choseReward', $secondData);
    }

    private function createActivity(): Activity
    {
        $activity = new Activity();
        $activity->setTitle('测试签到活动');
        $activity->setStartTime(CarbonImmutable::now()->subDay());
        $activity->setEndTime(CarbonImmutable::now()->addDays(7));
        $activity->setTimes(7);
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::ACCRUED);

        return $activity;
    }

    private function createRecord(Activity $activity, UserInterface $user, bool $hasAward = false, int $checkinTimes = 1, ?\DateTimeInterface $checkinDate = null): Record
    {
        $record = new Record();
        $record->setActivity($activity);
        $record->setUser($user);
        $record->setCheckinDate($checkinDate ?? CarbonImmutable::now());
        $record->setCheckinTimes($checkinTimes);
        $record->setHasAward($hasAward);

        return $record;
    }

    private function createReward(Activity $activity): Reward
    {
        $reward = new Reward();
        $reward->setActivity($activity);
        $reward->setCanShowPrize(true);
        $reward->setName('测试奖励');
        $reward->setTimes(1);
        $reward->setType(RewardType::COUPON);
        $reward->setValue('1');
        $reward->setRewardGetType(RewardGetType::AND);

        return $reward;
    }

    private function createAward(Record $record, Reward $reward): Award
    {
        $award = new Award();
        $award->setRecord($record);
        $award->setReward($reward);

        // 设置双向关系
        $record->addAward($award);

        return $award;
    }
}
