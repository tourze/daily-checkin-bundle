<?php

declare(strict_types=1);

namespace DailyCheckinBundle\Tests;

use DailyCheckinBundle\DailyCheckinBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DailyCheckinBundle::class)]
#[RunTestsInSeparateProcesses]
final class DailyCheckinBundleTest extends AbstractBundleTestCase
{
}
