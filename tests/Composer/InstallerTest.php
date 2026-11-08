<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Composer;

use Composer\IO\NullIO;
use Composer\Script\ScriptEvents;
use Mammatus\Groups\Composer\Installer;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\Composer\GenerativePluginTooling\Helper\Order;
use WyriHaximus\TestUtilities\TestCase;

final class InstallerTest extends TestCase
{
    #[Test]
    public function getSubscribedEvents(): void
    {
        $events = Installer::getSubscribedEvents();

        self::assertSame(
            [ScriptEvents::PRE_AUTOLOAD_DUMP => ['findGroups', Order::LAST]],
            $events,
        );
    }

    #[Test]
    public function activate(): void
    {
        $installer = new Installer();
        $installer->activate(new ComposerSub(), new NullIO());
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Installer::class, $installer);
    }

    #[Test]
    public function deactivate(): void
    {
        $installer = new Installer();
        $installer->deactivate(new ComposerSub(), new NullIO());
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Installer::class, $installer);
    }

    #[Test]
    public function uninstall(): void
    {
        $installer = new Installer();
        $installer->uninstall(new ComposerSub(), new NullIO());
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Installer::class, $installer);
    }
}
