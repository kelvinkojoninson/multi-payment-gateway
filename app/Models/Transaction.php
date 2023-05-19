<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'invoice_id',
        'status',
        'reason',
        'amount',
        'payment_service',
        'payment_service_attributes',
    ];

    /**
     * Get the invoice that owns the InvoiceItem
     */
    public function invoice(): BelongsTo
    {

        return $this->belongsTo(Invoice::class);
    }

    //mutator and accessor
    protected function paymentServiceAttributes(): Attribute
    {
        return Attribute::make(

            //automatically decode the stored JSON value into an associative array when accessing it
            get: fn (string $value) => json_decode($value, true),

            //automatically encode the provided array into JSON before setting the attribute.
            set: fn (string $value) => $this->attributes['payment_service_attributes'] = json_encode($value)
        );
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['payment_service_attributes'];
}
