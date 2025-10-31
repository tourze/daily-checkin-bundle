<?php

namespace DailyCheckinBundle\EventSubscriber;

use Carbon\CarbonImmutable;
use DailyCheckinBundle\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Activity::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Activity::class)]
class ActivityListener
{
    public function prePersist(Activity $object): void
    {
        $this->ensureDateValid($object);
    }

    public function preUpdate(Activity $object): void
    {
        $this->ensureDateValid($object);
    }

    /**
     * 检查开始时间和结束时间
     */
    public function ensureDateValid(Activity $object): void
    {
        if (null === $object->getStartTime() || null === $object->getEndTime()) {
            return;
        }

        $startTime = CarbonImmutable::parse($object->getStartTime());
        $endTime = CarbonImmutable::parse($object->getEndTime());
        if ($startTime->greaterThan($endTime)) {
            throw new \InvalidArgumentException('结束时间不应该早于开始时间');
        }
    }
}
