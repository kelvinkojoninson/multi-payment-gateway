<?php

namespace App\Models;

use App\Helpers\Actor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'payer_type' => Actor::class,
        'payer_details' => 'array',
        'paid_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    /**
     * Get all of the invoiceItems for the Invoice
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all transactions for the Invoice
     */
    public function transactions()
    {

        return $this->hasMany(Transaction::class);

    }
}
