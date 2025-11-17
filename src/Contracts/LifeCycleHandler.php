<?php

declare(strict_types=1);

namespace Mammatus\Groups\Contracts;

interface LifeCycleHandler
{
    public static function group(): string;

    public function start(): void;

    public function stop(): void;
}
