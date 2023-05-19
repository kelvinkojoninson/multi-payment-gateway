<?php

namespace App\Enums;

enum TheTellerProcessingCode: string
{
    case MOBILE_MONEY_PAYMENT = '000200';
    case CARD_PAYMENT = '000000';
}
