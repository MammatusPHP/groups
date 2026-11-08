<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Composer;

use Mammatus\DevApp\Groups\ClassWithGroup;
use Mammatus\DevApp\Groups\LifeIsLife;
use Mammatus\Groups\Attributes\Group as GroupAttribute;
use Mammatus\Groups\Composer\Collector;
use Mammatus\Groups\Composer\Group;
use Mammatus\Groups\Composer\LifeCycleHandler;
use Mammatus\Groups\Fallback\DealingWIthLife;
use PHPUnit\Framework\Attributes\Test;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\Composer\Factory\MakeLocatorForComposerJsonAndInstalledJson;
use WyriHaximus\Composer\GenerativePluginTooling\Composer\ASTLocatorStore;
use WyriHaximus\TestUtilities\TestCase;

final class CollectorTest extends TestCase
{
    private static function reflector(): DefaultReflector
    {
        return new DefaultReflector(
            (new MakeLocatorForComposerJsonAndInstalledJson())(
                __DIR__ . '/../../',
                ASTLocatorStore::ASTLocator(),
            ),
        );
    }

    #[Test]
    public function collectsGroupAttribute(): void
    {
        $collector = new Collector();

        $reflector       = self::reflector();
        $reflectionClass = $reflector->reflectClass(GroupAttribute::class);
        $items           = [...$collector->collect($reflectionClass)];

        self::assertCount(0, $items);
    }

    #[Test]
    public function collectsClassWithGroupAttribute(): void
    {
        $collector = new Collector();

        $reflector       = self::reflector();
        $reflectionClass = $reflector->reflectClass(ClassWithGroup::class);
        $items           = [...$collector->collect($reflectionClass)];

        self::assertCount(1, $items);
        self::assertInstanceOf(Group::class, $items[0]);
        self::assertSame('test-group', $items[0]->group->name);
    }

    #[Test]
    public function collectsLifeCycleHandler(): void
    {
        $collector = new Collector();

        $reflector       = self::reflector();
        $reflectionClass = $reflector->reflectClass(DealingWIthLife::class);
        $items           = [...$collector->collect($reflectionClass)];

        self::assertCount(1, $items);
        self::assertInstanceOf(LifeCycleHandler::class, $items[0]);
        self::assertSame(DealingWIthLife::class, $items[0]->lifeCycleHandler);
    }

    #[Test]
    public function collectsGroupAndLifeCycleHandler(): void
    {
        $collector = new Collector();

        $reflector       = self::reflector();
        $reflectionClass = $reflector->reflectClass(LifeIsLife::class);
        $items           = [...$collector->collect($reflectionClass)];

        self::assertCount(1, $items);
        self::assertInstanceOf(LifeCycleHandler::class, $items[0]);
        self::assertSame(LifeIsLife::class, $items[0]->lifeCycleHandler);
    }
}
