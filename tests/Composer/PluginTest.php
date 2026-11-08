<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Composer;

use Mammatus\DevApp\Groups\LifeIsLife;
use Mammatus\Groups\Attributes\Group as GroupAttribute;
use Mammatus\Groups\Composer\Collector;
use Mammatus\Groups\Composer\Group;
use Mammatus\Groups\Composer\LifeCycleHandler;
use Mammatus\Groups\Composer\Plugin;
use Mammatus\Groups\Fallback\DealingWIthLife;
use Mammatus\Groups\Type;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Operators\Class\LogicalOr;
use WyriHaximus\Composer\GenerativePluginTooling\Filter\Package\ComposerJsonHasItemWithSpecificValue;
use WyriHaximus\Composer\GenerativePluginTooling\LogStages;
use WyriHaximus\TestUtilities\TestCase;

use function copy;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class PluginTest extends TestCase
{
    #[Test]
    public function pluginName(): void
    {
        self::assertSame('mammatus/groups', Plugin::name());
    }

    #[Test]
    public function log(): void
    {
        self::assertSame('Locating groups', Plugin::log(LogStages::Init));
        self::assertSame('An error occurred: %s', Plugin::log(LogStages::Error));
        self::assertSame('Found %d group(s)', Plugin::log(LogStages::Collected));
        self::assertSame(
            'Generated static abstract Groups manager and Groups list in %s second(s)',
            Plugin::log(LogStages::Completion),
        );
    }

    #[Test]
    public function filters(): void
    {
        $filters = [...(new Plugin())->filters()];

        self::assertCount(2, $filters);
        self::assertInstanceOf(ComposerJsonHasItemWithSpecificValue::class, $filters[0]);
        self::assertInstanceOf(LogicalOr::class, $filters[1]);

        // Verify the filter value is true (kills TrueValue mutant)
        $reflection    = new ReflectionClass($filters[0]);
        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setAccessible(true);
        self::assertTrue($valueProperty->getValue($filters[0]));
    }

    #[Test]
    public function collectors(): void
    {
        $collectors = [...(new Plugin())->collectors()];

        self::assertCount(1, $collectors);
        self::assertInstanceOf(Collector::class, $collectors[0]);
    }

    #[Test]
    public function compileWithNoItems(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        $plugin->compile($tempDir);

        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');
        self::assertFileExists($tempDir . '/src/Fallback/DealingWIthLife.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);
        self::assertStringContainsString('final class Groups', $content);

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithNormalGroup(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);
        mkdir($tempDir . '/src', recursive: true);
        mkdir($tempDir . '/src/Fallback', recursive: true);

        // Pre-create files that should be removed by Remove::fileOnlyIfItExists
        file_put_contents($tempDir . '/src/Groups.php', 'old');
        file_put_contents($tempDir . '/src/SpawnDaemons.php', 'old');
        file_put_contents($tempDir . '/src/Fallback/old.php', 'old');

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        $groupAttribute = new GroupAttribute(Type::Normal, 'app');
        $group          = new Group($groupAttribute);

        $plugin->compile($tempDir, $group);

        // Verify old files were removed and regenerated at the correct path (kills concat mutants)
        // Fallback directory is preserved when there's a Normal group (not wiped)
        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');
        self::assertFileExists($tempDir . '/src/Fallback/old.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);
        self::assertStringContainsString('final class Groups', $content);

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithDaemonGroup(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        $normalGroupAttribute = new GroupAttribute(Type::Normal, 'app');
        $normalGroup          = new Group($normalGroupAttribute);
        $daemonGroupAttribute = new GroupAttribute(Type::Daemon, 'music');
        $daemonGroup          = new Group($daemonGroupAttribute);
        $daemonHandler        = new LifeCycleHandler(LifeIsLife::class);

        $plugin->compile($tempDir, $normalGroup, $daemonGroup, $daemonHandler);

        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);
        self::assertStringContainsString('music', $content);
        self::assertStringContainsString('app', $content);

        $spawnContent = file_get_contents($tempDir . '/src/SpawnDaemons.php');
        self::assertIsString($spawnContent);
        self::assertStringContainsString('LifeIsLife', $spawnContent);

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithDealingWIthLifeHandler(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        // Pass a DealingWIthLife handler - it should be filtered out by array_filter
        $dealingWIthLifeHandler = new LifeCycleHandler(DealingWIthLife::class);

        $plugin->compile($tempDir, $dealingWIthLifeHandler);

        // DealingWIthLife should be filtered out, so no items remain, triggering the fallback path
        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');
        self::assertFileExists($tempDir . '/src/Fallback/DealingWIthLife.php');

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithMultipleNormalAndDaemonGroups(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        // Multiple normal groups and daemon groups to trigger sorting/dedup in buildLoopUpMap
        $normalGroup1 = new Group(new GroupAttribute(Type::Normal, 'alpha'));
        $normalGroup2 = new Group(new GroupAttribute(Type::Normal, 'beta'));
        $daemonGroup1 = new Group(new GroupAttribute(Type::Daemon, 'music'));
        $daemonGroup2 = new Group(new GroupAttribute(Type::Daemon, 'video'));
        $handler1     = new LifeCycleHandler(LifeIsLife::class);

        $plugin->compile($tempDir, $normalGroup1, $normalGroup2, $daemonGroup1, $daemonGroup2, $handler1);

        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);

        // Verify loopUpMap is sorted (ksort) - alpha comes before beta, music before video
        // The Twig template renders arrays with newlines, so check for key order
        self::assertStringContainsString("'alpha' => [\n                'alpha',\n                'beta',\n                ]", $content);
        self::assertStringContainsString("'beta' => [\n                'beta',\n                ]", $content);
        // Daemon groups should list normal groups in sorted order
        self::assertStringContainsString("'music' => [\n                'alpha',\n                'beta',\n                ]", $content);
        self::assertStringContainsString("'video' => [\n                'alpha',\n                'beta',\n                ]", $content);

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithNonGroupItem(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        // Pass a LifeCycleHandler without a Group - triggers continue in first foreach
        $handler = new LifeCycleHandler(LifeIsLife::class);

        $plugin->compile($tempDir, $handler);

        // No normal group, so fallback is triggered
        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');
        self::assertFileExists($tempDir . '/src/Fallback/DealingWIthLife.php');

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithNonGroupBeforeGroup(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        // LifeCycleHandler first (non-Group), then a Normal Group
        // If continue→break, the loop would stop at the handler and miss the group
        $handler     = new LifeCycleHandler(LifeIsLife::class);
        $normalGroup = new Group(new GroupAttribute(Type::Normal, 'app'));

        $plugin->compile($tempDir, $handler, $normalGroup);

        // The normal group should be found, so no fallback DealingWIthLife template
        self::assertFileExists($tempDir . '/src/Groups.php');
        self::assertFileExists($tempDir . '/src/SpawnDaemons.php');
        self::assertFileDoesNotExist($tempDir . '/src/Fallback/DealingWIthLife.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);
        self::assertStringContainsString("'app'", $content);

        $this->removeDirectory($tempDir);
    }

    #[Test]
    public function compileWithDaemonBeforeNormal(): void
    {
        $plugin = new Plugin();

        $tempDir = sys_get_temp_dir() . '/plugin_test_' . uniqid();
        mkdir($tempDir . '/etc/generated_templates', recursive: true);

        copy(__DIR__ . '/../../etc/generated_templates/Groups.php.twig', $tempDir . '/etc/generated_templates/Groups.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/SpawnDaemons.php.twig', $tempDir . '/etc/generated_templates/SpawnDaemons.php.twig');
        copy(__DIR__ . '/../../etc/generated_templates/DealingWIthLife.php.twig', $tempDir . '/etc/generated_templates/DealingWIthLife.php.twig');

        // Daemon group first (non-Normal), then Normal group
        // If continue→break on line 81, the loop would stop at daemon and miss the normal group
        // causing $hasNormalGroup to be false, triggering the fallback path
        $daemonGroup = new Group(new GroupAttribute(Type::Daemon, 'music'));
        $normalGroup = new Group(new GroupAttribute(Type::Normal, 'app'));
        $handler     = new LifeCycleHandler(LifeIsLife::class);

        $plugin->compile($tempDir, $daemonGroup, $normalGroup, $handler);

        // Normal group was found, so no fallback DealingWIthLife template should be generated
        self::assertFileDoesNotExist($tempDir . '/src/Fallback/DealingWIthLife.php');

        $content = file_get_contents($tempDir . '/src/Groups.php');
        self::assertIsString($content);
        self::assertStringContainsString("'app'", $content);
        self::assertStringContainsString("'music'", $content);

        $this->removeDirectory($tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $path  = $file->getPathname();
            $isDir = $file->isDir();
            if ($isDir) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
