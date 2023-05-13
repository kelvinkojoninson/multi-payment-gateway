<?php

namespace App\Services;

use Illuminate\Http\Request;

interface PaymentService
{
    public function processPaystackPayment(float $amount, string $email, string $reference);
    public function verifyPaystackTransaction(string $reference);
}
