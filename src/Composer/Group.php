<?php

declare(strict_types=1);

namespace Mammatus\Groups\Composer;

use JsonSerializable;
use Mammatus\Groups\Attributes\Group as GroupAttribute;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;

final readonly class Group implements ItemContract, JsonSerializable
{
    public function __construct(
        public GroupAttribute $group,
    ) {
    }

    /** @return array{group: GroupAttribute} */
    public function jsonSerialize(): array
    {
        return [
            'group' => $this->group,
        ];
    }
}
