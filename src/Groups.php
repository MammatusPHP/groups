<?php

declare(strict_types=1);

namespace Mammatus\Groups;

use Mammatus\Groups\Contracts\LifeCycleHandler;
use Mammatus\Groups\Generated\AbstractGroups;
use Mammatus\LifeCycleEvents\Shutdown;
use Psr\Container\ContainerInterface;
use WyriHaximus\Broadcast\Contracts\AsyncListener;

final class Groups extends AbstractGroups implements AsyncListener
{
    /** @var array<string, array<int, LifeCycleHandler>> */
    private array $groups = [];

    /** @phpstan-ignore ergebnis.noParameterWithContainerTypeDeclaration */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function boot(string $group): void
    {
        $this->groups[$group] = [];
        foreach ($this->lifeCycleHandlers($group) as $handler) {
            /** @phpstan-ignore assign.propertyType */
            $this->groups[$group][] = $this->container->get($handler);
        }

        foreach ($this->groups[$group] as $handler) {
            /** @phpstan-ignore method.nonObject */
            $handler->start();
        }
    }

    public function shutdown(Shutdown $shutdown): void
    {
        foreach ($this->groups as $group) {
            foreach ($group as $handler) {
                $handler->stop();
            }
        }
    }
}
