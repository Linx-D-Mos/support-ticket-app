<?php

namespace App\Enums;

enum EventEnum : string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted'; 
}