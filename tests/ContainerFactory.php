<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups;

use ColinODell\PsrTestLogger\TestLogger;
use Mammatus\DevApp\Groups\LifeIsLife;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class ContainerFactory
{
    public static function create(): ContainerInterface
    {
        $container                         = new Container();
        $container[TestLogger::class]      = new TestLogger();
        $container[LoggerInterface::class] = $container[TestLogger::class];
        $container[LifeIsLife::class]      = new LifeIsLife($container[LoggerInterface::class]);

        return new PsrContainer($container);
    }
}
