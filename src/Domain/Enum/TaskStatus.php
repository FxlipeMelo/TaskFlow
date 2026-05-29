<?php

namespace App\Domain\Enum;

enum TaskStatus: string
{
    case OPEN = 'open';
    case FINISHED = 'finished';
    case DELETED = 'deleted';
}
