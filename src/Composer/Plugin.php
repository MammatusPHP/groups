<?php

declare(strict_types=1);

namespace Mammatus\Groups\Composer;

use Mammatus\Groups\Attributes\Group as GroupAttribute;
use Mammatus\Groups\Contracts\LifeCycleHandler as LifeCycleHandlerContract;
use Mammatus\Groups\Type;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Class\HasAttributes;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Class\ImplementsInterface;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Operators\LogicalOr;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Package\ComposerJsonHasItemWithSpecificValue;
use WyriHaximus\Composer\GenerativePluginTooling\GenerativePlugin;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\Remove;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\TwigFile;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;
use WyriHaximus\Composer\GenerativePluginTooling\LogStages;

use function array_key_exists;
use function str_increment;

final class Plugin implements GenerativePlugin
{
    public static function name(): string
    {
        return 'mammatus/groups';
    }

    public static function log(LogStages $stage): string
    {
        return match ($stage) {
            LogStages::Init => 'Locating groups',
            LogStages::Error => 'An error occurred: %s',
            LogStages::Collected => 'Found %d group(s)',
            LogStages::Completion => 'Generated static abstract Groups manager and Groups list in %s second(s)',
        };
    }

    /** @inheritDoc */
    public function filters(): iterable
    {
        yield new ComposerJsonHasItemWithSpecificValue('mammatus.has-groups', true);
        yield from LogicalOr::create(
            new ImplementsInterface(LifeCycleHandlerContract::class),
            new HasAttributes(GroupAttribute::class),
        );
    }

    /** @inheritDoc */
    public function collectors(): iterable
    {
        yield new Collector();
    }

    public function compile(string $rootPath, ItemContract ...$items): void
    {
        $groups             = [];
        $daemons            = [];
        $daemonPropertyName = 'a';

        foreach ($items as $item) {
            if ($item instanceof Group) {
                if (! array_key_exists($item->group->name, $groups)) {
                    $groups[$item->group->name] = [
                        'handlers' => [],
                    ];
                }

                $groups[$item->group->name]['group'] = $item;
            }

            if (! ($item instanceof LifeCycleHandler)) {
                continue;
            }

            if (! array_key_exists($item->lifeCycleHandler::group(), $groups)) {
                $groups[$item->lifeCycleHandler::group()] = [
                    'handlers' => [],
                ];
            }

            $groups[$item->lifeCycleHandler::group()]['handlers'][] = $item;
        }

        Remove::directoryContents($rootPath . '/src/Generated');

        TwigFile::render(
            $rootPath . '/etc/generated_templates/AbstractGroups.php.twig',
            $rootPath . '/src/Generated/AbstractGroups.php',
            ['groups' => $groups],
        );

        foreach ($groups as $group) {
            if (! array_key_exists('group', $group)) {
                continue;
            }

            if ($group['group']->group->type === Type::Daemon) {
                foreach ($group['handlers'] as $handler) {
                    $daemons[$daemonPropertyName] = $handler;
                }
            }

            $daemonPropertyName = str_increment($daemonPropertyName);
        }

        TwigFile::render(
            $rootPath . '/etc/generated_templates/SpawnDaemons.php.twig',
            $rootPath . '/src/Generated/SpawnDaemons.php',
            ['daemons' => $daemons],
        );
    }
}
