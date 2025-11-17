<?php

declare(strict_types=1);

namespace Mammatus\DevApp\Groups;

use Mammatus\Groups\Attributes\Group;
use Mammatus\Groups\Type;

#[Group(Type::Daemon, 'music')]
final readonly class Music
{
}
