<?php

declare(strict_types=1);

namespace Mammatus\DevApp\Groups;

use Mammatus\Groups\Contracts\LifeCycleHandler;
use Psr\Log\LoggerInterface;

final readonly class LifeIsLife implements LifeCycleHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function group(): string
    {
        return 'music';
    }

    public function start(): void
    {
        $this->logger->info('[Life is life] Starting');
    }

    public function stop(): void
    {
        $this->logger->info('[Life is life] Stopping');
    }
}
