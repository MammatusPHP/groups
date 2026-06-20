<?php

declare(strict_types=1);

namespace Mammatus\Groups;

/** @api */
enum Type: string
{
    case Daemon = 'daemon';
    case Normal = 'normal';
}
