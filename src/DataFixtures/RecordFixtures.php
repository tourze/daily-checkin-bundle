<?php

namespace DailyCheckinBundle\DataFixtures;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class RecordFixtures extends Fixture
{
    public const BASIC_RECORD = 'record-basic';

    public function load(ObjectManager $manager): void
    {
        $record = new Record();
        $record->setCheckinDate(CarbonImmutable::now());
        $record->setCheckinTimes(1);
        $record->setHasAward(false);

        // 设置时间戳和创建者信息
        $record->setCreateTime(new \DateTimeImmutable());
        $record->setUpdateTime(new \DateTimeImmutable());
        $record->setCreatedBy('test_user');
        $record->setUpdatedBy('test_user');

        $manager->persist($record);
        $manager->flush();

        $this->addReference(self::BASIC_RECORD, $record);
    }
}
