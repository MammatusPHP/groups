<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Composer;

use Mammatus\Groups\Composer\LifeCycleHandler;
use Mammatus\Groups\Fallback\DealingWIthLife;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\TestUtilities\TestCase;

final class LifeCycleHandlerTest extends TestCase
{
    #[Test]
    public function constructAndSerialize(): void
    {
        $handler = new LifeCycleHandler(DealingWIthLife::class);

        self::assertSame(DealingWIthLife::class, $handler->lifeCycleHandler);

        $serialized = $handler->jsonSerialize();
        self::assertSame(['lifeCycleHandler' => DealingWIthLife::class], $serialized);
    }
}
