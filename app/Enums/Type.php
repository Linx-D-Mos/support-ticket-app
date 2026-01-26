<?php

namespace App\Enums;

enum Type: string
{
    case INCIDENT = 'incident';
    case REQUEST = 'request';
    case BUG = 'bug';
    case QUESTION = 'question';
}
