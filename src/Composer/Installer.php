<?php

declare(strict_types=1);

namespace Mammatus\Groups\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use WyriHaximus\Composer\GenerativePluginTooling\GenerativePluginExecutioner;

use const PHP_INT_MIN;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    /** @return array<string, array<int, int|string>> */
    public static function getSubscribedEvents(): array
    {
        return [ScriptEvents::PRE_AUTOLOAD_DUMP => ['findGroups', PHP_INT_MIN + 69]];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    /**
     * Called before every dump autoload, generates a fresh PHP class.
     *
     * @phpstan-ignore shipmonk.deadMethod
     */
    public static function findGroups(Event $event): void
    {
        GenerativePluginExecutioner::execute($event->getComposer(), $event->getIO(), new Plugin());
    }
}
