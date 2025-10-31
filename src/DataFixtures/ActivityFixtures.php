<?php

namespace DailyCheckinBundle\DataFixtures;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Enum\CheckinType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class ActivityFixtures extends Fixture
{
    public const BASIC_ACTIVITY = 'activity-basic';

    public function load(ObjectManager $manager): void
    {
        $activity = new Activity();
        $activity->setTitle('基础签到活动');
        $activity->setValid(true);
        $activity->setCheckinType(CheckinType::CONTINUE);
        $activity->setStartTime(CarbonImmutable::now());
        $activity->setEndTime(CarbonImmutable::now()->addDays(30));
        $activity->setTimes(7);

        // 设置一些唯一的值以避免与测试冲突
        $activity->setShareTitle('DataFixture分享标题');
        $activity->setSharePicture('datafixture-share.jpg');
        $activity->setSharePath('datafixture/share/path');
        $activity->setZoneShareTitle('DataFixture空间分享标题');
        $activity->setZoneSharePicture('datafixture-zone.jpg');

        $manager->persist($activity);
        $manager->flush();

        $this->addReference(self::BASIC_ACTIVITY, $activity);
    }
}
