<?php

declare(strict_types=1);

namespace Mammatus\Groups;

final readonly class Group
{
    public function __construct(
        public Type $type,
        public string $name,
    ) {
    }
}
