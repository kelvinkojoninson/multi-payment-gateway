<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GlobalPaymentService implements PaymentService
{
    /**
     * Process payment using Paystack API.
     *
     * @param float $amount The payment amount.
     * @param string $email The customer's email address.
     * @param string $reference The payment reference.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the status code, message, and authorization URL.
     */
    public function processPaystackPayment(float $amount, string $email, string $reference)
    {
        try {
            // Set the URL for Paystack payment initialization
            $url = env('PAYSTACK_INITIALIZE_URL');

            // Prepare the data to be sent in the request payload
            $data = [
                'amount' => round($amount, 2) * 100, // Convert the amount to kobo (cents) and round to the nearest integer
                'email' => $email, // Customer's email address
                'reference' => $reference, // Payment reference
                'currency' => env('PAYSTACK_CURRENCY'), // Currency for the payment
                'callback_url' => env('PAYSTACK_CALLBACK_URL') // Callback URL for Paystack to send payment updates to
                // Add any other required parameters for the payment request
            ];

            // Set the headers for the API request
            $headers = [
                'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'), // Set the authorization header with Paystack secret key
                'Content-Type: application/json', // Set the content type header to indicate JSON data
            ];

            // Initialize a cURL session
            $ch = curl_init();

            // Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $url); // Set the URL to which the request will be sent
            curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Set the request payload with JSON-encoded data
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the request headers
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string instead of outputting it directly

            // Execute the cURL request and retrieve the response
            $response = curl_exec($ch);

            // Close the cURL session
            curl_close($ch);

            // Parse the response from Paystack API into an associative array
            $response = json_decode($response, true);

            // Check if the payment initialization was successful
            if (!$response['status']) {
                // Log the error message using the Laravel Log facade
                Log::error("INITIALIZE-PAYSTACK", [
                    'statusCode' => 500,
                    'message' => $response['message']
                ]);

                // Return a JSON response indicating the error with a status code
                return response()->json([
                    'statusCode' => 500,
                    'message' => $response['message'],
                ]);
            }

            // Log the successful payment initialization
            Log::info("INITIALIZE-PAYSTACK", [
                'statusCode' => 200,
                'message' => "Payment initialized",
                'data' => $response['data']['authorization_url']
            ]);

            // Return a JSON response with the status code, message, and authorization URL
            return response()->json([
                'statusCode' => 200,
                'message' => "Payment initialized",
                'data' => $response['data']['authorization_url']
            ]);
        } catch (\Throwable $e) {
            // If an error occurs, log the error details using the Laravel Log facade
            Log::error('INITIALIZE-PAYSTACK', [
                'statusCode' => 500,
                'message' => 'Paystack initialization system error',
                'error' => [
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);

            // Return an error response with a message and a status code
            return response()->json([
                'statusCode' => 500,
                'message' => 'Paystack initialization system error',
                'error' => [
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);
        }
    }

    /**
     * Process payment transaction verification with Paystack API.
     *
     * @param string $reference The payment reference.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the status code, message, and authorization URL.
     */
    public function verifyPaystackTransaction(string $reference)
    {
        try {
            // Make the API request to verify the transaction
            $verificationUrl = env('PAYSTACK_VERIFICATION_URL') .'/'. $reference;
            $headers = [
                'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type: application/json',
            ];

            // Initialize a cURL session
            $ch = curl_init();

            // Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $verificationUrl); // Set the URL to which the request will be sent
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the request headers
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string instead of outputting it directly

            // Execute the cURL request and retrieve the response
            $response = curl_exec($ch);

            // Close the cURL session
            curl_close($ch);

            // Parse the response from Paystack API into an associative array
            $response = json_decode($response, true);

            // Check if the verification was successful
            if ($response['status'] !== true && $response['data']['status'] !== 'success') {
                // Log the error message using the Laravel Log facade
                Log::error("VERIFY-PAYSTACK-TRANSACTION", [
                    'statusCode' => 500,
                    'message' => 'Transaction verification failed'
                ]);
                
                return redirect()->route('home')->with([
                    'status' => 'failed',
                    'message' => 'Transaction failed"'
                ]);
            } 

            // Log the successful payment initialization
            Log::info("VERIFY-PAYSTACK-TRANSACTION", [
                'statusCode' => 200,
                'message' => "Transaction completed successfully",
                'data' => $response
            ]);            

            return redirect()->route('home')->with([
                'status' => 'success',
                'message' => 'Transaction completed successfully"'
            ]);
        } catch (\Throwable $e) {
            // If an error occurs, log the error details using the Laravel Log facade
            Log::error('VERIFY-PAYSTACK-TRANSACTION', [
                'statusCode' => 500,
                'message' => 'Payment verification failed',
                'error' => [
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);

            // Return an error response with a message and a status code
            return redirect()->route('home')->with([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]); 
        }
    }
}
