<?php

namespace DailyCheckinBundle\Tests\Service;

use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Service\CheckinPrizeService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * @internal
 */
#[CoversClass(CheckinPrizeService::class)]
#[RunTestsInSeparateProcesses]
final class CheckinPrizeServiceTest extends AbstractIntegrationTestCase
{
    private CheckinPrizeService $service;
    private UserManagerInterface $userManager;

    protected function onSetUp(): void
    {
        $this->service = self::getService(CheckinPrizeService::class);
        $this->userManager = self::getService(UserManagerInterface::class);
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(CheckinPrizeService::class, $this->service);
    }

    public function testSendPrizeWithCouponReward(): void
    {
        // 使用 UserManagerInterface 创建真实用户
        $user = $this->userManager->createUser('test-user-' . uniqid());

        // 创建真实的奖品实体
        $reward = new Reward();
        $reward->setType(RewardType::COUPON);
        $reward->setValue('test-coupon');

        // 创建真实的记录实体
        $record = new Record();
        $record->setUser($user);
        $record->setCheckinTimes(1);

        // 调用被测试方法，验证不抛出异常
        $this->service->sendPrize($reward, $record);

        // 如果执行到这里说明没有抛出异常，测试通过
        $this->assertInstanceOf(CheckinPrizeService::class, $this->service);
    }

    public function testSendPrizeWithCreditReward(): void
    {
        // 使用 UserManagerInterface 创建真实用户
        $user = $this->userManager->createUser('test-user-' . uniqid());

        // 创建真实的奖品实体
        $reward = new Reward();
        $reward->setType(RewardType::CREDIT);
        $reward->setValue('100');

        // 创建真实的记录实体
        $record = new Record();
        $record->setUser($user);
        $record->setCheckinTimes(2);

        // 调用被测试方法，验证不抛出异常
        $this->service->sendPrize($reward, $record);

        // 如果执行到这里说明没有抛出异常，测试通过
        $this->assertInstanceOf(CheckinPrizeService::class, $this->service);
    }
}
