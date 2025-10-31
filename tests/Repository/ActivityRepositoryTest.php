<?php

namespace DailyCheckinBundle\Tests\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Repository\ActivityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template-extends AbstractRepositoryTestCase<Activity>
 * @internal
 */
#[CoversClass(ActivityRepository::class)]
#[RunTestsInSeparateProcesses]
final class ActivityRepositoryTest extends AbstractRepositoryTestCase
{
    private ActivityRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ActivityRepository::class);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        $activity1 = $this->createActivityForTest(['times' => 1]);
        $activity2 = $this->createActivityForTest(['times' => 10]); // 使用比 DataFixtures 更大的值
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $result = $this->repository->findOneBy([], ['times' => 'DESC']);

        $this->assertInstanceOf(Activity::class, $result);
        $this->assertEquals(10, $result->getTimes());
    }

    public function testFindByWithNullableShareTitle(): void
    {
        $activity1 = $this->createActivityForTest(['shareTitle' => 'Test Title']);
        $activity2 = $this->createActivityForTest(['shareTitle' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithTitle = $this->repository->findBy(['shareTitle' => 'Test Title']);
        $resultsWithNullTitle = $this->repository->findBy(['shareTitle' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithTitle));
        $this->assertActivityFoundWithValue($resultsWithTitle, $activity1, 'getShareTitle', 'Test Title');

        $this->assertGreaterThanOrEqual(1, count($resultsWithNullTitle));
        $this->assertActivityFoundWithNull($resultsWithNullTitle, $activity2, 'getShareTitle');
    }

    public function testFindByWithNullableSharePicture(): void
    {
        $activity1 = $this->createActivityForTest(['sharePicture' => 'test.jpg']);
        $activity2 = $this->createActivityForTest(['sharePicture' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithPicture = $this->repository->findBy(['sharePicture' => 'test.jpg']);
        $resultsWithNullPicture = $this->repository->findBy(['sharePicture' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithPicture));
        $this->assertActivityFoundWithValue($resultsWithPicture, $activity1, 'getSharePicture', 'test.jpg');

        $this->assertGreaterThanOrEqual(1, count($resultsWithNullPicture));
        $this->assertActivityFoundWithNull($resultsWithNullPicture, $activity2, 'getSharePicture');
    }

    public function testCountWithNullableSharePath(): void
    {
        $activity1 = $this->createActivityForTest(['sharePath' => 'test/path']);
        $activity2 = $this->createActivityForTest(['sharePath' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithPath = $this->repository->count(['sharePath' => 'test/path']);
        $countWithNullPath = $this->repository->count(['sharePath' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithPath);
        $this->assertGreaterThanOrEqual(1, $countWithNullPath);
    }

    public function testFindByWithNullableZoneShareTitle(): void
    {
        $activity1 = $this->createActivityForTest(['zoneShareTitle' => 'Zone Title']);
        $activity2 = $this->createActivityForTest(['zoneShareTitle' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithTitle = $this->repository->findBy(['zoneShareTitle' => 'Zone Title']);
        $resultsWithNullTitle = $this->repository->findBy(['zoneShareTitle' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithTitle));
        $this->assertActivityFoundWithValue($resultsWithTitle, $activity1, 'getZoneShareTitle', 'Zone Title');

        $this->assertGreaterThanOrEqual(1, count($resultsWithNullTitle));
        $this->assertActivityFoundWithNull($resultsWithNullTitle, $activity2, 'getZoneShareTitle');
    }

    public function testFindByWithNullableZoneSharePicture(): void
    {
        $activity1 = $this->createActivityForTest(['zoneSharePicture' => 'zone.jpg']);
        $activity2 = $this->createActivityForTest(['zoneSharePicture' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithPicture = $this->repository->findBy(['zoneSharePicture' => 'zone.jpg']);
        $resultsWithNullPicture = $this->repository->findBy(['zoneSharePicture' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithPicture));
        $this->assertActivityFoundWithValue($resultsWithPicture, $activity1, 'getZoneSharePicture', 'zone.jpg');

        $this->assertGreaterThanOrEqual(1, count($resultsWithNullPicture));
        $this->assertActivityFoundWithNull($resultsWithNullPicture, $activity2, 'getZoneSharePicture');
    }

    public function testCountWithNullableZoneShareTitle(): void
    {
        $activity1 = $this->createActivityForTest(['zoneShareTitle' => 'Zone Title']);
        $activity2 = $this->createActivityForTest(['zoneShareTitle' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithTitle = $this->repository->count(['zoneShareTitle' => 'Zone Title']);
        $countWithNullTitle = $this->repository->count(['zoneShareTitle' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithTitle);
        $this->assertGreaterThanOrEqual(1, $countWithNullTitle);
    }

    public function testCountWithNullableSharePicture(): void
    {
        $activity1 = $this->createActivityForTest(['sharePicture' => 'test.jpg']);
        $activity2 = $this->createActivityForTest(['sharePicture' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithPicture = $this->repository->count(['sharePicture' => 'test.jpg']);
        $countWithNullPicture = $this->repository->count(['sharePicture' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithPicture);
        $this->assertGreaterThanOrEqual(1, $countWithNullPicture);
    }

    public function testCountWithNullableZoneSharePicture(): void
    {
        $activity1 = $this->createActivityForTest(['zoneSharePicture' => 'zone.jpg']);
        $activity2 = $this->createActivityForTest(['zoneSharePicture' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithPicture = $this->repository->count(['zoneSharePicture' => 'zone.jpg']);
        $countWithNullPicture = $this->repository->count(['zoneSharePicture' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithPicture);
        $this->assertGreaterThanOrEqual(1, $countWithNullPicture);
    }

    public function testFindByWithNullableValid(): void
    {
        $activity1 = $this->createActivityForTest(['valid' => true]);
        $activity2 = $this->createActivityForTest();
        $activity2->setValid(null); // 直接设置为null

        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithValid = $this->repository->findBy(['valid' => true]);
        $resultsWithNullValid = $this->repository->findBy(['valid' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithValid));
        $this->assertGreaterThanOrEqual(1, count($resultsWithNullValid));

        $this->assertActivityFoundWithValue($resultsWithValid, $activity1, 'isValid', true);
        $this->assertActivityFoundWithNull($resultsWithNullValid, $activity2, 'isValid');
    }

    public function testCountWithNullableValid(): void
    {
        $activity1 = $this->createActivityForTest(['valid' => true]);
        $activity2 = $this->createActivityForTest();
        $activity2->setValid(null); // 直接设置为null

        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithValid = $this->repository->count(['valid' => true]);
        $countWithNullValid = $this->repository->count(['valid' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithValid);
        $this->assertGreaterThanOrEqual(1, $countWithNullValid);
    }

    public function testFindByWithNullableFields(): void
    {
        $activity1 = $this->createActivityForTest(['sharePath' => 'test/path']);
        $activity2 = $this->createActivityForTest(['sharePath' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $resultsWithPath = $this->repository->findBy(['sharePath' => 'test/path']);
        $resultsWithNullPath = $this->repository->findBy(['sharePath' => null]);

        $this->assertGreaterThanOrEqual(1, count($resultsWithPath));
        $this->assertActivityFoundWithValue($resultsWithPath, $activity1, 'getSharePath', 'test/path');

        $this->assertGreaterThanOrEqual(1, count($resultsWithNullPath));
        $this->assertActivityFoundWithNull($resultsWithNullPath, $activity2, 'getSharePath');
    }

    public function testCountWithNullableFields(): void
    {
        $activity1 = $this->createActivityForTest(['shareTitle' => 'Test Title']);
        $activity2 = $this->createActivityForTest(['shareTitle' => null]);
        $this->persistAndFlush($activity1);
        $this->persistAndFlush($activity2);

        $countWithTitle = $this->repository->count(['shareTitle' => 'Test Title']);
        $countWithNullTitle = $this->repository->count(['shareTitle' => null]);

        $this->assertGreaterThanOrEqual(1, $countWithTitle);
        $this->assertGreaterThanOrEqual(1, $countWithNullTitle);
    }

    public function testSaveWithFlush(): void
    {
        $activity = $this->createActivityForTest();

        $this->repository->save($activity, true);

        $this->assertNotNull($activity->getId());
        $found = $this->repository->find($activity->getId());
        $this->assertInstanceOf(Activity::class, $found);
        $this->assertEquals($activity->getTitle(), $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $activity = $this->createActivityForTest();

        $this->repository->save($activity, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($activity->getId());
        $found = $this->repository->find($activity->getId());
        $this->assertInstanceOf(Activity::class, $found);
        $this->assertEquals($activity->getTitle(), $found->getTitle());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createActivityForTest(array $overrides = []): Activity
    {
        $activity = new Activity();

        $this->setBasicActivityProperties($activity, $overrides);
        $this->setActivityShareProperties($activity, $overrides);

        return $activity;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function setBasicActivityProperties(Activity $activity, array $overrides): void
    {
        $title = $overrides['title'] ?? 'Test Activity ' . uniqid();
        self::assertIsString($title);
        $activity->setTitle($title);

        $startTime = $overrides['startTime'] ?? new \DateTimeImmutable();
        self::assertInstanceOf(\DateTimeInterface::class, $startTime);
        $activity->setStartTime($startTime);

        $endTime = $overrides['endTime'] ?? new \DateTimeImmutable('+30 days');
        self::assertInstanceOf(\DateTimeInterface::class, $endTime);
        $activity->setEndTime($endTime);

        $activity->setTimes($this->extractInt($overrides, 'times', 7));
        $activity->setValid($this->extractNullableBool($overrides, 'valid', true));

        if (array_key_exists('checkinType', $overrides)) {
            $activity->setCheckinType($this->extractCheckinType($overrides, 'checkinType'));
        } else {
            $activity->setCheckinType(CheckinType::CONTINUE);
        }

        $this->setActivityMetadata($activity);
    }

    private function setActivityMetadata(Activity $activity): void
    {
        $activity->setCreateTime(new \DateTimeImmutable());
        $activity->setUpdateTime(new \DateTimeImmutable());
        $activity->setCreatedBy('test_user');
        $activity->setUpdatedBy('test_user');
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function setActivityShareProperties(Activity $activity, array $overrides): void
    {
        if (array_key_exists('sharePath', $overrides)) {
            $activity->setSharePath($this->extractNullableString($overrides, 'sharePath'));
        }
        if (array_key_exists('shareTitle', $overrides)) {
            $activity->setShareTitle($this->extractNullableString($overrides, 'shareTitle'));
        }
        if (array_key_exists('sharePicture', $overrides)) {
            $activity->setSharePicture($this->extractNullableString($overrides, 'sharePicture'));
        }
        if (array_key_exists('zoneShareTitle', $overrides)) {
            $activity->setZoneShareTitle($this->extractNullableString($overrides, 'zoneShareTitle'));
        }
        if (array_key_exists('zoneSharePicture', $overrides)) {
            $activity->setZoneSharePicture($this->extractNullableString($overrides, 'zoneSharePicture'));
        }
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
    private function extractNullableBool(array $data, string $key, ?bool $default = null): ?bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $value = $data[$key];

        return \is_bool($value) || null === $value ? $value : $default;
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

    /**
     * @param array<string, mixed> $data
     */
    private function extractCheckinType(array $data, string $key): ?CheckinType
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];

        return $value instanceof CheckinType || null === $value ? $value : null;
    }

    protected function createNewEntity(): Activity
    {
        return $this->createActivityForTest([
            'title' => 'Test Activity ' . uniqid(),
            'startTime' => new \DateTimeImmutable('2023-01-01 00:00:00'),
            'endTime' => new \DateTimeImmutable('2023-12-31 23:59:59'),
            'times' => 1,
            'valid' => true,
            'checkinType' => CheckinType::CONTINUE,
        ]);
    }

    /**
     * @param array<Activity> $results
     */
    private function assertActivityFoundWithValue(array $results, Activity $expectedActivity, string $getter, mixed $expectedValue): void
    {
        $foundActivity = false;
        foreach ($results as $result) {
            if ($result->getId() === $expectedActivity->getId()) {
                $actualValue = match ($getter) {
                    'getShareTitle' => $result->getShareTitle(),
                    'getSharePicture' => $result->getSharePicture(),
                    'getSharePath' => $result->getSharePath(),
                    'getZoneShareTitle' => $result->getZoneShareTitle(),
                    'getZoneSharePicture' => $result->getZoneSharePicture(),
                    'isValid' => $result->isValid(),
                    default => throw new \InvalidArgumentException("Unknown getter: $getter"),
                };
                $this->assertSame($expectedValue, $actualValue);
                $foundActivity = true;
                break;
            }
        }
        $this->assertTrue($foundActivity, 'Expected to find activity with ' . $getter . ' = ' . var_export($expectedValue, true));
    }

    /**
     * @param array<Activity> $results
     */
    private function assertActivityFoundWithNull(array $results, Activity $expectedActivity, string $getter): void
    {
        $foundActivity = false;
        foreach ($results as $result) {
            if ($result->getId() === $expectedActivity->getId()) {
                $actualValue = match ($getter) {
                    'getShareTitle' => $result->getShareTitle(),
                    'getSharePicture' => $result->getSharePicture(),
                    'getSharePath' => $result->getSharePath(),
                    'getZoneShareTitle' => $result->getZoneShareTitle(),
                    'getZoneSharePicture' => $result->getZoneSharePicture(),
                    'isValid' => $result->isValid(),
                    default => throw new \InvalidArgumentException("Unknown getter: $getter"),
                };
                $this->assertNull($actualValue);
                $foundActivity = true;
                break;
            }
        }
        $this->assertTrue($foundActivity, 'Expected to find activity with null ' . $getter);
    }

    protected function getRepository(): ActivityRepository
    {
        return $this->repository;
    }
}
