<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\PHPSan;

use Mammatus\Groups\Fallback\DealingWIthLife;
use Mammatus\Groups\PHPSan\ShipMonkDeadCode;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;
use WyriHaximus\TestUtilities\TestCase;

final class ShipMonkDeadCodeTest extends TestCase
{
    #[Test]
    public function marksLifeCycleHandlerMethodAsUsed(): void
    {
        $provider = new ShipMonkDeadCode();
        $method   = new ReflectionMethod(DealingWIthLife::class, 'start');

        $result = $provider->shouldMarkMethodAsUsed($method);

        self::assertInstanceOf(VirtualUsageData::class, $result);
        self::assertSame('Class is a LifeCycleHandler', $result->getNote());
    }

    #[Test]
    public function returnsNullForNonLifeCycleHandler(): void
    {
        $provider = new ShipMonkDeadCode();
        $method   = new ReflectionMethod(self::class, 'returnsNullForNonLifeCycleHandler');

        $result = $provider->shouldMarkMethodAsUsed($method);

        self::assertNull($result);
    }
}
