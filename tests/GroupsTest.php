<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups;

use ColinODell\PsrTestLogger\TestLogger;
use InvalidArgumentException;
use Mammatus\Groups\Groups;
use Mammatus\LifeCycleEvents\Shutdown;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use WyriHaximus\TestUtilities\TestCase;

use function md5;
use function time;

final class GroupsTest extends TestCase
{
    #[Test]
    public function nonExistentGroup(): void
    {
        $groupName = md5((string) time());
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('No life cycle handlers found for group ' . $groupName);

        $groups = new Groups(
            new class () implements ContainerInterface {
                /**
                 * @param class-string<T> $id
                 *
                 * @return T
                 *
                 * @template T
                 *
                 * @phpstan-ignore shipmonk.missingNativeReturnTypehint,typeCoverage.returnTypeCoverage,method.childParameterType
                 */
                public function get(string $id) // phpcs:disable
                {
                    return new $id();
                }

                public function has(string $id): bool
                {
                    return false;
                }
            },
        );

        $groups->boot($groupName);
    }

    #[Test]
    public function music(): void
    {
        $container = ContainerFactory::create();
        $logger = $container->get(TestLogger::class);
        self::assertInstanceOf(TestLogger::class, $logger);
        $groups = new Groups($container);
        self::assertFalse($logger->hasInfoRecords());

        $groups->boot('music');
        self::assertTrue($logger->hasInfoRecords());
        self::assertCount(1, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Starting'));
        self::assertFalse($logger->hasInfoThatContains('Stopping'));

        $groups->shutdown(new Shutdown());
        self::assertCount(2, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Starting'));
        self::assertTrue($logger->hasInfoThatContains('Stopping'));
    }

    #[Test]
    public function findMusicInGroups(): void
    {
        $groups = [...Groups::groups()];

        self::assertArrayHasKey('music', $groups);
    }
}
