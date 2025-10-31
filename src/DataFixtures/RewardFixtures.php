<?php

namespace DailyCheckinBundle\DataFixtures;

use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class RewardFixtures extends Fixture
{
    public const BASIC_REWARD = 'reward-basic';

    public function load(ObjectManager $manager): void
    {
        $reward = new Reward();
        $reward->setName('基础奖励');
        $reward->setType(RewardType::CREDIT);
        $reward->setValue('10');
        $reward->setTimes(1);
        $reward->setQuantity(100);
        $reward->setDayLimit(1);
        $reward->setIsDefault(false);
        $reward->setCanShowPrize(true);
        $reward->setRewardGetType(RewardGetType::AND);

        $manager->persist($reward);
        $manager->flush();

        $this->addReference(self::BASIC_REWARD, $reward);
    }
}
