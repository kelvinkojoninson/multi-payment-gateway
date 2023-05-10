<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'amount' => "required|regex:/^\d+(\.\d{1,2})?$/|min:1.00",
            'reference' => "required",
        ], [
            "email.required" => "Email is required!",
            "email.email" => "Email must be a valid email required!",
            "amount.required" => "Amount is required!",
            "amount.regex" => "Amount must be a valid amount like '12' or '12.5' or '12.05'!",
            "amount.min" => "A minimum of GHS 1.00 is required!",
            "reference.required" => "Reference is required!",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "failed",
                "message" => $validator->errors()->first()
            ]);
        }

        // replace reference with $request->reference
        $reference = bin2hex(random_bytes(8));
        return $this->paymentService->processPaystackPayment($request->amount, $request->email, $reference);
    }

    public function handlePaystackCallback(Request $request)
    {
        try {
            // Get the payment details from the webhook
            $paymentDetails = $request->all();

            // Check if the transaction was successful
            if ($paymentDetails['data']['status'] !== 'success') {
                return response()->json([
                    "status" => "failed",
                    "message" => "Payment failed"
                ]);
            }

            // Save the payment details in the database
            // $transaction = new Transaction();
            // $transaction->reference = $paymentDetails['data']['reference'];
            // $transaction->status = $paymentDetails['data']['status'];
            // $transaction->amount = $paymentDetails['data']['amount'] / 100; // convert pesewas to cedis
            // $transaction->save();

            // Send a success response to Paystack
            return response()->json([
                'status' => 'success',
                'message' => 'Payment received'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "Payment failed.",
                "error" => [
                    "msg" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ]
            ]);
        }
    }
}
