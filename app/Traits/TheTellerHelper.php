<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait TheTellerHelper
{
    public function processMomoPayment(float $amount, $transactionId, $momoNetwork, $momoNumber, $voucherCode)
    {
        $testAmount = 5.00;

        $json = [
            'amount' => $this->getPaymentAmount(round(config('services.payment.env') === 'test' ? $testAmount : $amount, 2)),
            'processing_code' => '000200',
            'transaction_id' => $transactionId,
            'desc' => config('app.name'),
            'merchant_id' => config('services.payment.merchant_id'),
            'subscriber_number' => '233'.substr($momoNumber, -9),
            'r-switch' => $momoNetwork,
            'voucher_code' => $voucherCode,
        ];

        $apiUrl = 'https://'.config('services.payment.env').'.theteller.net/v1.1/transaction/process';
        $apiKey = config('services.payment.env') === 'test' ? config('services.payment.test_api_key') : config('services.payment.prod_api_key');

        $client = new Client();
        $res = $client->post($apiUrl, [
            'connect_timeout' => 300,
            'headers' => [
                'Authorization' => 'Basic '.$apiKey,
            ],
            'json' => $json,
        ]);

        return json_decode($res->getBody()->getContents(), true);
    }

    public function processMomoTransfer(float $amount, $transactionId, $accountIssuer, $accountNumber, $description)
    {
        $testAmount = 5.00;

        $json = [
            'pass_code' => config('services.payment.transfer_pass_code'),
            'amount' => $this->getPaymentAmount(round(config('services.payment.env') === 'test' ? $testAmount : $amount, 2)),
            'processing_code' => '404000',
            'transaction_id' => $transactionId,
            'desc' => $description,
            'r-switch' => 'FLT',
            'merchant_id' => config('services.payment.merchant_id'),
            'account_number' => $accountNumber,
            'account_issuer' => $accountIssuer,
        ];

        $apiUrl = 'https://'.config('services.payment.env').'.theteller.net/v1.1/transaction/process';
        $apiKey = config('services.payment.env') === 'test' ? config('services.payment.test_api_key') : config('services.payment.prod_api_key');

        $client = new Client();
        $res = $client->post($apiUrl, [
            'connect_timeout' => 300,
            'headers' => [
                'Authorization' => 'Basic '.$apiKey,
            ],
            'json' => $json,
        ]);

        return json_decode($res->getBody()->getContents(), true);
    }

    public function getPaymentAmount($amount)
    {
        $convertedAmount = ((float) $amount) * 100;
        $convertedAmount = str_pad($convertedAmount, 12, '0', STR_PAD_LEFT);

        return $convertedAmount;
    }
}
