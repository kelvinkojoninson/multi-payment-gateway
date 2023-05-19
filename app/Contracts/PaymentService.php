<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/*
    *This is meant to provide a common interface for working with multiple gateways
    *
    */
interface PaymentService
{
    /*
     * This method handles all pay requests.
     *
     *
     */
    public function processPayment(Request $request);

    public function momoPayment(Request $request);

    public function cardPayment(Request $request);

    /*
    * This method will handle feedback from gateway providers
    * There will be a route to this method which will be called by the gateway provider
    * It will check for the status and broadcast appropriate invoice status
    */
    public function handleGatewayCallBack(Request $request);

    public function verifyTransaction($transactionId);
}
