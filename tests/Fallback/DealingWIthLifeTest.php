<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Fallback;

use Mammatus\Groups\Fallback\DealingWIthLife;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\TestUtilities\TestCase;

final class DealingWIthLifeTest extends TestCase
{
    #[Test]
    public function group(): void
    {
        self::assertSame('app', DealingWIthLife::group());
    }

    #[Test]
    public function start(): void
    {
        $handler = new DealingWIthLife();
        $handler->start();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(DealingWIthLife::class, $handler);
    }

    #[Test]
    public function stop(): void
    {
        $handler = new DealingWIthLife();
        $handler->stop();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(DealingWIthLife::class, $handler);
    }
}
