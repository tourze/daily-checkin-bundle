<?php

namespace DailyCheckinBundle\Procedure;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Event\BeforeReturnCheckinAwardsEvent;
use DailyCheckinBundle\Repository\ActivityRepository;
use DailyCheckinBundle\Repository\RecordRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc(summary: '获取签到活动的奖励')]
#[MethodTag(name: '签到模块')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetCheckinAwards')]
class GetCheckinAwards extends BaseProcedure
{
    #[MethodParam(description: '签到活动ID')]
    public string $activityId;

    public function __construct(
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly RecordRepository $recordRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function execute(): array
    {
        $activity = $this->activityRepository->findOneBy([
            'id' => $this->activityId,
        ]);
        if (empty($activity)) {
            throw new ApiException('暂无活动');
        }

        $records = $this->recordRepository->findBy([
            'activity' => $activity,
            'user' => $this->security->getUser(),
        ], ['id' => 'DESC']);

        $list = [];
        foreach ($records as $record) {
            /** @var Award $award */
            foreach ($record->getAwards() as $award) {
                if (!$award->getReward()->getCanShowPrize()) {
                    continue;
                }
                $list[] = $award->retrieveApiArray();
            }
        }

        $event = new BeforeReturnCheckinAwardsEvent();
        $event->setResult($list);
        $event->setUser($this->security->getUser());
        $this->eventDispatcher->dispatch($event);

        return $event->getResult();
    }
}
