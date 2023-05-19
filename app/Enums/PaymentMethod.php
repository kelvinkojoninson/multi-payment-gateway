<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MOBILE_MONEY = 'Mobile Money';
    case CARD = 'Card';
    case STANDARD = 'Standard';
}
