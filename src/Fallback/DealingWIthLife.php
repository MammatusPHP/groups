<?php

declare(strict_types=1);

namespace Mammatus\Groups\Fallback;

use Mammatus\Groups\Contracts\LifeCycleHandler;

final class DealingWIthLife implements LifeCycleHandler
{
    public static function group(): string
    {
        return 'app';
    }

    public function start(): void
    {
        // No-op
    }

    public function stop(): void
    {
        // No-op
    }
}
