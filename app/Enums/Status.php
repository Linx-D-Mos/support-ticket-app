<?php

namespace App\Enums;

enum Status : string
{
    case OPEN = 'open';
    case INPROGRESS = 'in progress';
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
