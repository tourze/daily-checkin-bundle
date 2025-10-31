<?php

namespace DailyCheckinBundle\Tests\Service;

use DailyCheckinBundle\Entity\Record;
use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Service\CheckinPrizeService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CheckinPrizeService::class)]
#[RunTestsInSeparateProcesses]
final class CheckinPrizeServiceTest extends AbstractIntegrationTestCase
{
    private CheckinPrizeService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(CheckinPrizeService::class);
    }

    // InterfaceStub方法 - 简化测试中的接口实现

    /**
     * 创建UserInterface的简单stub实现
     *
     * @param non-empty-string $userIdentifier 用户标识符，默认为'test-user'
     * @param array<string> $roles 用户角色数组，默认为空数组
     */
    private function createUserStub(string $userIdentifier = 'test-user', array $roles = []): UserInterface
    {
        return new class($userIdentifier, $roles) implements UserInterface {
            /**
             * @param non-empty-string $userIdentifier
             * @param array<string> $roles
             */
            public function __construct(
                private string $userIdentifier,
                private array $roles,
            ) {
            }

            /**
             * @return array<string>
             */
            public function getRoles(): array
            {
                return $this->roles;
            }

            public function eraseCredentials(): void
            {
                // 空实现 - stub不需要真正的凭据管理
            }

            /**
             * @return non-empty-string
             */
            public function getUserIdentifier(): string
            {
                return $this->userIdentifier;
            }
        };
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(CheckinPrizeService::class, $this->service);
    }

    public function testSendPrizeWithCouponReward(): void
    {
        // 使用InterfaceStub方法创建用户
        $user = $this->createUserStub('test-user');

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
        // 使用InterfaceStub方法创建用户
        $user = $this->createUserStub('test-user');

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
