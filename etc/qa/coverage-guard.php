<?php

declare(strict_types=1);

use PhpParser\Node;
use ShipMonk\CoverageGuard\Config;
use ShipMonk\CoverageGuard\Excluder\ExcludedLineRange;
use ShipMonk\CoverageGuard\Excluder\ExclusionContext;
use ShipMonk\CoverageGuard\Excluder\ExecutableLineExcluder;
use ShipMonk\CoverageGuard\Excluder\IgnoreThrowNewExceptionLineExcluder;
use ShipMonk\CoverageGuard\Rule\EnforceCoverageForMethodsRule;

return (static function (): Config {
    $config = new Config();

    $config->addRule(new EnforceCoverageForMethodsRule(
        requiredCoveragePercentage: 80,
        minExecutableLines: 1,
    ));

    $config->addExecutableLineExcluder(new IgnoreThrowNewExceptionLineExcluder([
        RuntimeException::class,
    ]));

    // Installer::findGroups is a thin wrapper around GenerativePluginExecutioner::execute()
    // that cannot be unit-tested in isolation (requires full Composer setup)
    $config->addExecutableLineExcluder(new class implements ExecutableLineExcluder {
        public function getExcludedLineRange(Node $node, ExclusionContext $context): ExcludedLineRange|null
        {
            if (! $node instanceof Node\Stmt\ClassMethod) {
                return null;
            }

            if ($node->name->toString() !== 'findGroups') {
                return null;
            }

            if (str_contains($context->getFilePath(), 'Installer.php') === false) {
                return null;
            }

            return new ExcludedLineRange($node->getStartLine(), $node->getEndLine());
        }
    });

    return $config;
})();
