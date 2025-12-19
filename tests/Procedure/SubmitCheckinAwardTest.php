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
use DailyCheckinBundle\Procedure\SubmitCheckinAward;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(SubmitCheckinAward::class)]
#[RunTestsInSeparateProcesses]
final class SubmitCheckinAwardTest extends AbstractProcedureTestCase
{
    private SubmitCheckinAward $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(SubmitCheckinAward::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(SubmitCheckinAward::class, $this->procedure);
    }

    public function testImplementsCorrectInterfaces(): void
    {
        $this->assertInstanceOf('Tourze\JsonRPCLockBundle\Procedure\LockableProcedure', $this->procedure);
    }

    public function testExecuteWithNonExistentRecord(): void
    {
        $param = new \DailyCheckinBundle\Param\SubmitCheckinAwardParam(
            rewardId: 'some-reward-id',
            recordId: 'non-existent-record-id'
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('签到记录不存在');

        $this->procedure->execute($param);
    }

    public function testExecuteWithRecordWithoutAward(): void
    {
        $user = $this->createNormalUser();
        $activity = $this->createActivity();
        $record = $this->createRecord($activity, $user, hasAward: false);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($record);

        $recordId = $record->getId();
        $this->assertNotNull($recordId, 'Record ID should not be null after persistence');
        $param = new \DailyCheckinBundle\Param\SubmitCheckinAwardParam(
            rewardId: 'some-reward-id',
            recordId: $recordId
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('无法获得奖品');

        $this->procedure->execute($param);
    }

    public function testExecuteWithAlreadyAwardedRecord(): void
    {
        $user = $this->createNormalUser();
        $activity = $this->createActivity();
        $reward = $this->createReward($activity);
        $record = $this->createRecord($activity, $user, hasAward: true);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($reward);
        $this->persistAndFlush($record);

        $award = $this->createAward($record, $reward);
        $this->persistAndFlush($award);

        $recordId = $record->getId();
        $rewardId = $reward->getId();
        $this->assertNotNull($recordId, 'Record ID should not be null after persistence');
        $this->assertNotNull($rewardId, 'Reward ID should not be null after persistence');
        $param = new \DailyCheckinBundle\Param\SubmitCheckinAwardParam(
            rewardId: $rewardId,
            recordId: $recordId
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('已获得奖品');

        $this->procedure->execute($param);
    }

    public function testExecuteWithNonExistentReward(): void
    {
        $user = $this->createNormalUser();
        $activity = $this->createActivity();
        $record = $this->createRecord($activity, $user, hasAward: true);

        $this->persistAndFlush($activity);
        $this->persistAndFlush($record);

        $recordId = $record->getId();
        $this->assertNotNull($recordId, 'Record ID should not be null after persistence');
        $param = new \DailyCheckinBundle\Param\SubmitCheckinAwardParam(
            rewardId: 'non-existent-reward-id',
            recordId: $recordId
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('奖励不存在');

        $this->procedure->execute($param);
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

    private function createRecord(Activity $activity, UserInterface $user, bool $hasAward = false, int $checkinTimes = 1): Record
    {
        $record = new Record();
        $record->setActivity($activity);
        $record->setUser($user);
        $record->setCheckinDate(CarbonImmutable::now());
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
