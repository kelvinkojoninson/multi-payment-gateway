<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\PaymentService;

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
                "statusCode" => 500,
                "message" => $validator->errors()->first()
            ]);
        }

        try {
            // Replace reference with $request->reference
            $reference = strtoupper(bin2hex(random_bytes(10)));
            return $this->paymentService->processPaystackPayment($request->amount, $request->email, $reference);
        } catch (\Throwable $e) {
            // Return an error response with a message and a status code
            return response()->json([
                'statusCode' => 500,
                'message' => 'Payment initialization error',
                'error' => [
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);
        }
    }

    public function handleCallback(Request $request)
    {
        if (!$request->reference) {
            // Return a JSON response indicating the error with a status code
            return response()->json([
                'statusCode' => 500,
                'message' => 'Transaction reference is required'
            ]);
        }

        return $this->paymentService->verifyPaystackTransaction($request->reference);
    }
}
