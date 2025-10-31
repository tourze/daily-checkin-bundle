<?php

namespace DailyCheckinBundle\DataFixtures;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class BasicActivityFixtures extends Fixture
{
    public const DAILY_CHECKIN_ACTIVITY = 'daily-checkin-activity';

    public function load(ObjectManager $manager): void
    {
        $activity = new Activity();
        $activity->setTitle('日常抽奖活动');
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::CONTINUE);
        $activity->setStartTime(CarbonImmutable::now());
        $activity->setEndTime(CarbonImmutable::now()->addYears(10));

        $manager->persist($activity);

        // 保存奖品
        for ($i = 1; $i < 5; ++$i) {
            $reward = new Reward();
            $reward->setActivity($activity);
            $reward->setName("奖品{$i}");
            $reward->setType(RewardType::CREDIT);
            $reward->setValue((string) (10 + $i));
            $reward->setTimes($i);
            $reward->setRewardGetType(RewardGetType::OR);
            $manager->persist($reward);
        }

        $manager->flush();

        $this->addReference(self::DAILY_CHECKIN_ACTIVITY, $activity);
    }
}
