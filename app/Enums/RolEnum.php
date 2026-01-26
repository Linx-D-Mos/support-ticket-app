<?php

namespace App\Enums;

enum RolEnum : string
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case CUSTOMER = 'customer';
}
