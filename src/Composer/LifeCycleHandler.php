<?php

declare(strict_types=1);

namespace Mammatus\Groups\Composer;

use JsonSerializable;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;

final readonly class LifeCycleHandler implements ItemContract, JsonSerializable
{
    /** @param class-string<\Mammatus\Groups\Contracts\LifeCycleHandler> $lifeCycleHandler */
    public function __construct(
        public string $lifeCycleHandler,
    ) {
    }

    /** @return array{lifeCycleHandler: class-string<\Mammatus\Groups\Contracts\LifeCycleHandler>} */
    public function jsonSerialize(): array
    {
        return [
            'lifeCycleHandler' => $this->lifeCycleHandler,
        ];
    }
}
