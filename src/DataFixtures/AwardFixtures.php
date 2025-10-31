<?php

namespace DailyCheckinBundle\DataFixtures;

use DailyCheckinBundle\Entity\Award;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class AwardFixtures extends Fixture
{
    public const BASIC_AWARD = 'award-basic';

    public function load(ObjectManager $manager): void
    {
        $award = new Award();

        // 设置时间戳和创建者信息
        $award->setCreateTime(new \DateTimeImmutable());
        $award->setUpdateTime(new \DateTimeImmutable());
        $award->setCreatedBy('test_user');
        $award->setUpdatedBy('test_user');

        $manager->persist($award);
        $manager->flush();

        $this->addReference(self::BASIC_AWARD, $award);
    }
}
