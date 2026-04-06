<?php

namespace App\Enum;

enum TaskStatus: string
{
    case OPEN = 'open';
    case FINISHED = 'finished';
    case DELETED = 'deleted';
}
