<?php

namespace DailyCheckinBundle\Tests\Procedure;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Procedure\GetRecentlyCheckinRecords;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetRecentlyCheckinRecords::class)]
#[RunTestsInSeparateProcesses]
final class GetRecentlyCheckinRecordsTest extends AbstractProcedureTestCase
{
    private GetRecentlyCheckinRecords $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetRecentlyCheckinRecords::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetRecentlyCheckinRecords::class, $this->procedure);
    }

    public function testExecuteSuccessfully(): void
    {
        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $user1 = $this->createNormalUser('user1@example.com');
        $user2 = $this->createNormalUser('user2@example.com');

        $record1 = $this->createRecord($activity, $user1, CarbonImmutable::now(), 1);
        $record2 = $this->createRecord($activity, $user2, CarbonImmutable::now()->subDay(), 1);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');
        $this->procedure->activityId = $activityId;
        $this->procedure->nums = 4;

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertCount(2, $result['data']);
        $firstItem = $result['data'][0] ?? null;
        $secondItem = $result['data'][1] ?? null;
        $this->assertIsArray($firstItem);
        $this->assertIsArray($secondItem);
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('id', $secondItem);
    }

    public function testExecuteWithNonExistentActivity(): void
    {
        $this->procedure->activityId = 'non-existent-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('暂无活动');

        $this->procedure->execute();
    }

    public function testExecuteWithNoRecords(): void
    {
        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');
        $this->procedure->activityId = $activityId;
        $this->procedure->nums = 4;

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertEmpty($result['data']);
    }

    public function testExecuteWithCustomNumsParameter(): void
    {
        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $user1 = $this->createNormalUser('user1@example.com');
        $user2 = $this->createNormalUser('user2@example.com');
        $user3 = $this->createNormalUser('user3@example.com');

        $record1 = $this->createRecord($activity, $user1, CarbonImmutable::now(), 1);
        $record2 = $this->createRecord($activity, $user2, CarbonImmutable::now()->subDay(), 1);
        $record3 = $this->createRecord($activity, $user3, CarbonImmutable::now()->subDays(2), 1);
        $this->persistAndFlush($record1);
        $this->persistAndFlush($record2);
        $this->persistAndFlush($record3);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');
        $this->procedure->activityId = $activityId;
        $this->procedure->nums = 2;

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertCount(2, $result['data']);
    }

    public function testExecuteWithLargeNumsParameter(): void
    {
        $activity = $this->createActivity();
        $this->persistAndFlush($activity);

        $user1 = $this->createNormalUser('user1@example.com');
        $record1 = $this->createRecord($activity, $user1, CarbonImmutable::now(), 1);
        $this->persistAndFlush($record1);

        $activityId = $activity->getId();
        $this->assertNotNull($activityId, 'Activity ID should not be null after persistence');
        $this->procedure->activityId = $activityId;
        $this->procedure->nums = 10;

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertCount(1, $result['data']);
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
