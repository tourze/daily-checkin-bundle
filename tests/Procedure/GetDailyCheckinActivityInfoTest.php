<?php

namespace DailyCheckinBundle\Tests\Procedure;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Param\GetDailyCheckinActivityInfoParam;
use DailyCheckinBundle\Procedure\GetDailyCheckinActivityInfo;
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
#[CoversClass(GetDailyCheckinActivityInfo::class)]
#[RunTestsInSeparateProcesses]
final class GetDailyCheckinActivityInfoTest extends AbstractProcedureTestCase
{
    private GetDailyCheckinActivityInfo $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetDailyCheckinActivityInfo::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetDailyCheckinActivityInfo::class, $this->procedure);
    }

    public function testExecuteWithValidActivity(): void
    {
        $user = $this->createNormalUser();

        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new GetDailyCheckinActivityInfoParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('activity', $result);
        $this->assertArrayHasKey('accumulatedDays', $result);
        $this->assertArrayHasKey('todayHadCheckin', $result);
        $this->assertFalse($result['todayHadCheckin']);
    }

    public function testExecuteWithNonExistentActivity(): void
    {
        $user = $this->createNormalUser();

        $param = new GetDailyCheckinActivityInfoParam('non-existent-id');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('暂无活动');

        $this->procedure->execute($param);
    }

    public function testExecuteWithAccruedActivityAndRecords(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity(CheckinType::ACCRUED);
        $this->persistAndFlush($activity);

        $record = $this->createRecord($activity, $user, CarbonImmutable::now(), 3);
        $this->persistAndFlush($record);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new GetDailyCheckinActivityInfoParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('record', $result);
        $this->assertGreaterThan(0, $result['accumulatedDays']);
        $this->assertTrue($result['todayHadCheckin']);
    }

    public function testExecuteWithContinuousActivityAndRecords(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity(CheckinType::CONTINUE);
        $this->persistAndFlush($activity);

        $record = $this->createRecord($activity, $user, CarbonImmutable::now(), 1);
        $this->persistAndFlush($record);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new GetDailyCheckinActivityInfoParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('dayRecords', $result);
        $this->assertSame(1, $result['accumulatedDays']);
        $this->assertTrue($result['todayHadCheckin']);
    }

    public function testExecuteWithContinuousActivityNoTodayCheckin(): void
    {
        $user = $this->createNormalUser();
        $this->persistAndFlush($user);

        // 模拟用户登录状态
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $activity = $this->createActivity(CheckinType::CONTINUE);
        $this->persistAndFlush($activity);

        $record = $this->createRecord($activity, $user, CarbonImmutable::yesterday(), 1);
        $this->persistAndFlush($record);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');

        $param = new GetDailyCheckinActivityInfoParam($activityId);
        $result = $this->procedure->execute($param);

        $this->assertArrayHasKey('dayRecords', $result);
        $this->assertSame(1, $result['accumulatedDays']);
        $this->assertFalse($result['todayHadCheckin']);
    }

    private function createActivity(CheckinType $checkinType = CheckinType::ACCRUED): Activity
    {
        $activity = new Activity();
        $activity->setTitle('测试签到活动');
        $activity->setStartTime(CarbonImmutable::now()->subDay());
        $activity->setEndTime(CarbonImmutable::now()->addDays(7));
        $activity->setTimes(7);
        $activity->setValid(true);
        $activity->setCheckinType($checkinType);

        return $activity;
    }

    private function createRecord(Activity $activity, UserInterface $user, CarbonImmutable $checkinDate, int $checkinTimes): Record
    {
        $record = new Record();
        $record->setActivity($activity);
        $record->setUser($user);
        $record->setCheckinDate($checkinDate);
        $record->setCheckinTimes($checkinTimes);
        $record->setHasAward(false);

        return $record;
    }
}
