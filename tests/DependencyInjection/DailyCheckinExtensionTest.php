<?php

namespace DailyCheckinBundle\Tests\DependencyInjection;

use DailyCheckinBundle\DependencyInjection\DailyCheckinExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DailyCheckinExtensionTest extends TestCase
{
    private DailyCheckinExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new DailyCheckinExtension();
        $this->container = new ContainerBuilder();
    }

    public function testInstanceOfExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);
        
        // 基本服务应该被加载
        $this->assertTrue($this->container->hasDefinition('DailyCheckinBundle\Service\CheckinPrizeService'));
    }

    public function testLoadWithEmptyConfig(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);
        
        $this->assertTrue($this->container->hasDefinition('DailyCheckinBundle\Service\CheckinPrizeService'));
    }
}