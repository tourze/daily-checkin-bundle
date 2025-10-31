<?php

namespace DailyCheckinBundle\Service;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('签到打卡')) {
            $item->addChild('签到打卡');
        }

        $checkinMenu = $item->getChild('签到打卡');
        if (null !== $checkinMenu) {
            $checkinMenu->addChild('打卡活动')->setUri($this->linkGenerator->getCurdListPage(Activity::class));
            $checkinMenu->addChild('签到记录')->setUri($this->linkGenerator->getCurdListPage(Record::class));
            $checkinMenu->addChild('奖品设置')->setUri($this->linkGenerator->getCurdListPage(Reward::class));
            $checkinMenu->addChild('奖励情况')->setUri($this->linkGenerator->getCurdListPage(Award::class));
        }
    }
}
