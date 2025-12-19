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
use DailyCheckinBundle\Procedure\GetCheckinAwards;
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
#[CoversClass(GetCheckinAwards::class)]
#[RunTestsInSeparateProcesses]
final class GetCheckinAwardsTest extends AbstractProcedureTestCase
{
    private GetCheckinAwards $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetCheckinAwards::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetCheckinAwards::class, $this->procedure);
    }

    public function testExecuteWithValidActivity(): void
    {
        $user = $this->createNormalUser();

        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetCheckinAwardsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $this->assertIsArray($result->toArray());
    }

    public function testExecuteWithNonExistentActivity(): void
    {
        $user = $this->createNormalUser();

        $param = new \DailyCheckinBundle\Param\GetCheckinAwardsParam('non-existent-id');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('暂无活动');

        $this->procedure->execute($param);
    }

    public function testExecuteWithRecordsAndAwards(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        $activity = $this->createActivity();
        $reward = $this->createReward($activity, canShowPrize: true);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);

        $record = $this->createRecord($activity, $user);
        $this->persistAndFlush($record);

        $award = $this->createAward($record, $reward);
        $this->persistAndFlush($award);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetCheckinAwardsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertNotEmpty($result->toArray());
    }

    public function testExecuteWithRecordsButNoShowablePrizes(): void
    {
        $user = $this->createNormalUser();

        $activity = $this->createActivity();
        $reward = $this->createReward($activity, canShowPrize: false);
        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);

        $record = $this->createRecord($activity, $user);
        $this->persistAndFlush($record);

        $award = $this->createAward($record, $reward);
        $this->persistAndFlush($award);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new \DailyCheckinBundle\Param\GetCheckinAwardsParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertEmpty($result->toArray());
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

    private function createReward(Activity $activity, bool $canShowPrize = true): Reward
    {
        $reward = new Reward();
        $reward->setActivity($activity);
        $reward->setCanShowPrize($canShowPrize);
        $reward->setName('测试奖励');
        $reward->setTimes(1);
        $reward->setType(RewardType::COUPON);
        $reward->setValue('1');
        $reward->setRewardGetType(RewardGetType::AND);

        return $reward;
    }

    private function createRecord(Activity $activity, UserInterface $user): Record
    {
        $record = new Record();
        $record->setActivity($activity);
        $record->setUser($user);
        $record->setCheckinDate(CarbonImmutable::now());
        $record->setCheckinTimes(1);
        $record->setHasAward(true);

        return $record;
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
