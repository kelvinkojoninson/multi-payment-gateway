<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';

    public function title(): string
    {
        return ucfirst($this->value);
    }
}
