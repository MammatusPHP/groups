<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Attributes;

use Mammatus\Groups\Attributes\Group;
use Mammatus\Groups\Type;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\TestUtilities\TestCase;

final class GroupTest extends TestCase
{
    #[Test]
    public function dTO(): void
    {
        $group = new Group(
            Type::Daemon,
            '+ALL',
        );

        self::assertSame(Type::Daemon->value, $group->type->value);
        self::assertSame('+ALL', $group->name);
    }
}
