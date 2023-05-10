<?php

namespace App\Traits;

class TheTellerPaymentService implements PaymentService
{
    public function processPaystackPayment(float $amount, string $email, string $reference)
    {
        // Call Paystack API to process payment
        $url = 'https://api.paystack.co/transaction/initialize';
        $fields = [
            'amount' => round($amount, 2) * 100,
            'email' => $email,
            'reference' => $reference,
            'currency' => 'GHS'
        ];
        $headers = [
            'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse response from Paystack API
        $response = json_decode($response, true);
        if (!$response['status']) {
            return response()->json([
                "status" => 'failed',
                "message" => $response['message'],
            ]);
        }

        // Return transaction ID
        return response()->json([
            "status" => "success",
            "message" => "Payment initialized",
            "data" => $response['data']['authorization_url']
        ]);
    }
}
