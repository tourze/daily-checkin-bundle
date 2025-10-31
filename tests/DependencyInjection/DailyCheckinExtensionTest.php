<?php

namespace DailyCheckinBundle\Tests\DependencyInjection;

use DailyCheckinBundle\DependencyInjection\DailyCheckinExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinExtension::class)]
final class DailyCheckinExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private DailyCheckinExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new DailyCheckinExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testInstanceOfExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function testLoadWithEmptyConfig(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);

        // 验证核心服务定义已注册
        $this->assertTrue(
            $this->container->hasDefinition('DailyCheckinBundle\Service\CheckinPrizeService'),
            'CheckinPrizeService should be registered'
        );

        // 验证服务定义可以被获取
        $definition = $this->container->getDefinition('DailyCheckinBundle\Service\CheckinPrizeService');
        $this->assertSame('DailyCheckinBundle\Service\CheckinPrizeService', $definition->getClass());
    }
}
