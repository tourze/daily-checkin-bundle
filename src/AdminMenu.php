<?php

namespace DailyCheckinBundle;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('签到打卡') === null) {
            $item->addChild('签到打卡');
        }

        $item->getChild('签到打卡')->addChild('打卡活动')->setUri($this->linkGenerator->getCurdListPage(Activity::class));
        $item->getChild('签到打卡')->addChild('签到记录')->setUri($this->linkGenerator->getCurdListPage(Record::class));
        $item->getChild('签到打卡')->addChild('奖励情况')->setUri($this->linkGenerator->getCurdListPage(Award::class));
    }
}
