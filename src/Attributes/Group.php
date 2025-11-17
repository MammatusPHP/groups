<?php

declare(strict_types=1);

namespace Mammatus\Groups\Attributes;

use Attribute;
use Mammatus\Groups\Type;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Group
{
    public function __construct(
        public Type $type,
        public string $name,
    ) {
    }
}
