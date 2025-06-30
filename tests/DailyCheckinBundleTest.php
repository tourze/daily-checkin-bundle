<?php

namespace DailyCheckinBundle\Tests;

use DailyCheckinBundle\DailyCheckinBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DailyCheckinBundleTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new DailyCheckinBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBundleName(): void
    {
        $bundle = new DailyCheckinBundle();
        $this->assertSame('DailyCheckinBundle', $bundle->getName());
    }
}