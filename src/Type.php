<?php

declare(strict_types=1);

namespace Mammatus\Groups;

enum Type: string
{
    case Daemon = 'daemon';
    case Normal = 'normal';
}
