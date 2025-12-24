<?php

declare(strict_types=1);

namespace Mammatus\Groups\Composer;

use Mammatus\Groups\Attributes\Group as GroupAttribute;
use Mammatus\Groups\Contracts\LifeCycleHandler as LifeCycleHandlerContract;
use Roave\BetterReflection\Reflection\ReflectionClass;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;
use WyriHaximus\Composer\GenerativePluginTooling\ItemCollector;

use function array_key_exists;

use const PHP_EOL;

final class Collector implements ItemCollector
{
    /** @return iterable<ItemContract> */
    public function collect(ReflectionClass $class): iterable
    {
        echo $class->getName(), PHP_EOL;
        $attributes = [];
        foreach (new \ReflectionClass($class->getName())->getAttributes() as $attributeReflection) {
            $attribute                       = $attributeReflection->newInstance();
            $attributes[$attribute::class][] = $attribute;
        }

        if (array_key_exists(GroupAttribute::class, $attributes)) {
            foreach ($attributes[GroupAttribute::class] as $group) {
                yield new Group(
                /** @phpstan-ignore argument.type */
                    $group,
                );
            }
        }

        if (! $class->implementsInterface(LifeCycleHandlerContract::class)) {
            return;
        }

        yield new LifeCycleHandler(
            /** @phpstan-ignore argument.type */
            $class->getName(),
        );
    }
}
