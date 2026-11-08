<?php

declare(strict_types=1);

namespace Mammatus\DevApp\Groups;

use Mammatus\Groups\Attributes\Group;
use Mammatus\Groups\Type;

#[Group(Type::Normal, 'test-group')]
final class ClassWithGroup
{
}
