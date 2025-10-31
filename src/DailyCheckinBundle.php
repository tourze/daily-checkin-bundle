<?php

namespace DailyCheckinBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\JsonRPCLockBundle\JsonRPCLockBundle;
use Tourze\JsonRPCSecurityBundle\JsonRPCSecurityBundle;

class DailyCheckinBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineIndexedBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            JsonRPCLockBundle::class => ['all' => true],
            JsonRPCSecurityBundle::class => ['all' => true],
        ];
    }
}
