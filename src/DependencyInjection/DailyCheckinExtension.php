<?php

namespace DailyCheckinBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class DailyCheckinExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
