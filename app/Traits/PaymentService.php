<?php

namespace App\Traits;

interface PaymentService
{
    public function processPaystackPayment(float $amount, string $email, string $reference);
}
