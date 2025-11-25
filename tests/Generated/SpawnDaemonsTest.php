<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Generated;

use ColinODell\PsrTestLogger\TestLogger;
use Mammatus\DevApp\Groups\LifeIsLife;
use Mammatus\Groups\Generated\SpawnDaemons;
use Mammatus\LifeCycleEvents\Boot;
use Mammatus\LifeCycleEvents\Shutdown;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\TestUtilities\TestCase;

final class SpawnDaemonsTest extends TestCase
{
    #[Test]
    public function flow(): void
    {
        $logger = new TestLogger();
        self::assertFalse($logger->hasInfoRecords());

        $lifeIsLife = new LifeIsLife($logger);
        self::assertFalse($logger->hasInfoRecords());

        $daemon = new SpawnDaemons($lifeIsLife);
        self::assertFalse($logger->hasInfoRecords());

        $daemon->boot(new Boot());
        self::assertTrue($logger->hasInfoRecords());
        self::assertCount(1, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Starting'));
        self::assertFalse($logger->hasInfoThatContains('Stopping'));

        $daemon->shutdown(new Shutdown());
        self::assertCount(2, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Starting'));
        self::assertTrue($logger->hasInfoThatContains('Stopping'));
    }
}
