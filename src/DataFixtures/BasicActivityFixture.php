<?php

namespace DailyCheckinBundle\DataFixtures;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Enum\RewardType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BasicActivityFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $activity = new Activity();
        $activity->setTitle('日常抽奖活动');
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::CONTINUE);
        $activity->setStartTime(CarbonImmutable::now());
        $activity->setEndTime(CarbonImmutable::now()->subYears(10));

        $manager->persist($activity);

        // 保存奖品
        for ($i = 1; $i < 5; ++$i) {
            $reward = new Reward();
            $reward->setActivity($activity);
            $reward->setName("奖品{$i}");
            $reward->setType(RewardType::CREDIT);
            $reward->setValue((string) (10 + $i));
            $reward->setTimes($i);
            $manager->persist($reward);
        }

        $manager->flush();

        $this->addReference('daily-checkin-activity', $activity);
    }
}
