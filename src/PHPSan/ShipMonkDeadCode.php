<?php

declare(strict_types=1);

namespace Mammatus\Groups\PHPSan;

use Mammatus\Groups\Contracts\LifeCycleHandler;
use Override;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

final class ShipMonkDeadCode extends ReflectionBasedMemberUsageProvider
{
    #[Override]
    public function shouldMarkMethodAsUsed(ReflectionMethod $method): VirtualUsageData|null
    {
        if ($method->getDeclaringClass()->implementsInterface(LifeCycleHandler::class)) {
            return VirtualUsageData::withNote('Class is a LifeCycleHandler');
        }

        return null;
    }
}
