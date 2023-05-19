<?php

namespace App\Services;

use App\Contracts\PaymentService;
use App\Enums\PaymentMethod;
use App\Enums\TheTellerProcessingCode;
use App\Enums\TransactionStatus;
use App\Helpers\PaymentStatus;
use App\Traits\TheTellerHelper;
use App\Traits\TransactionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TheTellerPaymentService implements PaymentService
{
    use TheTellerHelper;
    use TransactionHelper;

    const ACCEPTABLE_MOMO_PAYMENT_NETWORKS = ['MTN', 'VDF', 'ATL', 'TGO'];

    const ACCEPTABLE_CARDS = ['VIS', 'MAS'];

    const SUCCESS_STATUS = 'successful';

    const PAYMENT_SERVICE = 'TheTeller';

    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => ['required', 'exists:invoice'],
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => PaymentStatus::FAILED,
                'message' => $validator->errors()->first(),
            ]);
        }

        $transaction = $this->initiateTransaction($request->invoice_number);

        $transaction->payment_service = self::PAYMENT_SERVICE;

        $transaction->payment_service_attributes = [
            'payment_method' => PaymentMethod::STANDARD,
            'email' => $request->email,
        ];
        $transaction->save();

        $payload = [
            'merchant_id' => config('services.payment.merchant_id'),
            'transaction_id' => $transaction->transactionId,
            'desc' => config('app.name'),
            'amount' => $transaction->amount,
            'email' => $request->email,
            'redirect_url' => config('services.payment.callback_url'),
        ];

        $apiKey = config('services.payment.env') === 'test' ? config('services.payment.test_api_key') : config('services.payment.prod_api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$apiKey,
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
        ])->post('https://test.theteller.net/checkout/initiate', $payload);

        if ($response->failed()) {
            $this->updateTransactionStatus($transaction->transactionId, TransactionStatus::FAILED);

            return $response->body();
        } else {
            $this->updateTransactionStatus($transaction->transactionId, TransactionStatus::PROCESSING);

            return $response->json();
        }
    }

    public function momoPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => ['required', 'exist:invoice'],
            'momoNetwork' => ['required', 'string', Rule::in(self::ACCEPTABLE_MOMO_PAYMENT_NETWORKS)],
            'momoNumber' => ['required'],
            'voucherCode' => ['required_if:momo_network,VDF'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => PaymentStatus::FAILED,
                'message' => $validator->errors()->first(),
            ]);
        }

        $transaction = $this->initiateTransaction($request->invoice_number);

        $transaction->payment_service = self::PAYMENT_SERVICE;

        $transaction->payment_service_attributes = [
            'payment_method' => PaymentMethod::MOBILE_MONEY,
            'momo_network' => $request->momoNetwork,
            'momo_number' => $request->momoNumber,
        ];
        $transaction->save();

        return $this->processMomoPayment($transaction->amount, $transaction->transactionId, $request->momoNetwork, $request->momoNumber, $request->voucherCode);
    }

    // transactionId string	Unique transaction reference provided by you.
    // rSwitch	string	Account issuer or network on which the account to be debited resides.
    // cardHolder	string	Card holder name on card.
    // currency	string	Currency to charge card e.g GHS
    // pan string	card pan number on card
    // expMonth	true	string	Expiry month of card
    // expYear	true	string	Expiry year of card
    // cvv	true	string	CVV on back of card
    // 3d_url_response	true	string	Callback url to return your user to when transaction is completed

    public function cardPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => ['required'],
            'rSwitch' => ['required'],
            'cardHolder' => ['required'],
            'email' => ['required'],
            'pan' => ['required', 'max:19'],
            'expMonth' => ['required', 'max:2'],
            'expYear' => ['required', 'max:4'],
            'cvv' => ['required', 'min:3', 'max:3'],
            'currency' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => TransactionStatus::FAILED,
                'message' => $validator->errors()->first(),
            ]);
        }

        $transaction = $this->initiateTransaction($request->invoice_number);

        $transaction->payment_service = self::PAYMENT_SERVICE;

        $transaction->payment_service_attributes = [
            'payment_method' => PaymentMethod::CARD,
            'card_holder' => $request->cardHolder,
            'pan' => $request->pan,
            'currency' => $request->currency,
        ];

        $transaction->save();

        $payload = [
            'merchant_id' => config('services.payment.merchant_id'),
            'transaction_id' => $transaction->transactionId,
            'desc' => config('app.name'),
            'amount' => $transaction->amount,
            'email' => $request->email,
            'processing_code' => TheTellerProcessingCode::CARD_PAYMENT,
            'r-switch' => $request->rSwitch,
            'pan' => $request->pan,
            'exp_month' => $request->expMonth,
            'exp_year' => $request->expYear,
            'cvv' => $request->cvv,
            'currency' => $request->currency,
            'card_holder' => $request->cardHolder,
            '3d_url_response' => config('services.payment.callback_url'),
        ];

        $apiKey = config('services.payment.env') === 'test' ? config('services.payment.test_api_key') : config('services.payment.prod_api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$apiKey,
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
        ])->post('https://test.theteller.net/checkout/initiate', $payload);

        if ($response->failed()) {
            $this->updateTransactionStatus($transaction->transactionId, TransactionStatus::FAILED);

            return $response->body();
        } else {
            $this->updateTransactionStatus($transaction->transactionId, TransactionStatus::PROCESSING);

            return $response->json();
        }
    }

    public function verifyTransaction($transactionId)
    {
        $response = Http::withHeaders([
            'Cache-Control' => 'no-cache',
            'merchant_id' => config('services.payment.merchant_id'),
        ])->get('https://test.theteller.net/v1.1/users/transactions/'.$transactionId.'/status');

        if ($response->failed()) {
            return $response->body();
        } else {
            return $response->json();
        }
    }

    public function handleGatewayCallBack(Request $request)
    {
        // Get the transaction ID from the query parameters
        $transactionId = $request->input('transaction_id');

        // Get the status of the payment from the query parameters
        $status = $request->input('status');

        // Get the reason for the payment status from the query parameters
        $reason = $request->input('reason');

        // Check if the payment was successful
        if ($status === self::SUCCESS_STATUS) {

            // If the payment was successful, update the transaction status in your database and return a success response
            $this->updateTransactionStatus($transactionId, TransactionStatus::SUCCESSFUL);

            $this->markInvoiceAsPaid($transactionId);

            return response()->json([
                'status' => $status,
                'message' => $reason,
                'transactionId' => $transactionId,
            ]);
        } else {
            // If the payment failed, update the transaction status in your database and return an error response
            $this->updateTransactionStatus($transactionId, TransactionStatus::FAILED);

            return response()->json([
                'status' => $status,
                'message' => $reason,
                'transactionId' => $transactionId,
            ]);
        }
    }
}
