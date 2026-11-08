<?php

declare(strict_types=1);

namespace Mammatus\Tests\Groups\Composer;

use Mammatus\Groups\Attributes\Group as GroupAttribute;
use Mammatus\Groups\Composer\Group;
use Mammatus\Groups\Type;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\TestUtilities\TestCase;

final class GroupTest extends TestCase
{
    #[Test]
    public function constructAndSerialize(): void
    {
        $groupAttribute = new GroupAttribute(Type::Daemon, 'music');
        $group          = new Group($groupAttribute);

        self::assertSame($groupAttribute, $group->group);

        $serialized = $group->jsonSerialize();
        self::assertSame(['group' => $groupAttribute], $serialized);
    }
}
