<?php

namespace App\Traits;

use App\Enums\TransactionStatus;
use App\Events\InvoiceStatusUpdated;
use App\Helpers\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\Response;

trait TransactionHelper
{
    public function initiateTransaction($invoiceNumber): Transaction
    {
        //Generate 12 unique numbers
        $uniqueTransactionId = date('Y-m-d H:i:s');
        $uniqueTransactionId = substr($uniqueTransactionId, 2);
        $uniqueTransactionId = str_replace(['-', ' ', ':'], '', $uniqueTransactionId);

        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        return Transaction::create([
            'transaction_id' => $uniqueTransactionId,
            'status' => TransactionStatus::PENDING->value,
            'reason' => 'Transaction is pending processing',
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
        ]);
    }

    public function updateTransactionStatus($transactionId, TransactionStatus $status)
    {
        return Transaction::where('transaction_id', $transactionId)->update([
            'status' => $status->value,
        ]);
    }

    public function markInvoiceAsPaid($successfulTransactionId)
    {
        $transaction = Transaction::where('transaction_id', $successfulTransactionId)->first();

        if (! $transaction) {
            return Response::json([
                'status' => TransactionStatus::FAILED->value,
                'message' => 'The transaction provided is not valid',
            ]);
        }

        $transactionInvoice = $transaction->invoice;

        if (! $transactionInvoice) {
            return Response::json([
                'status' => TransactionStatus::FAILED->value,
                'message' => 'The transaction provided does not belong to an invoice',
            ]);
        }

        $isTransactionValid = $this->verifyTransaction($transaction->id);

        if ($isTransactionValid->status) {
            $transactionInvoice->status = InvoiceStatus::PAID->value;
            $transactionInvoice->save();

            event(new InvoiceStatusUpdated($transactionInvoice));

            return Response::json([
                'status' => TransactionStatus::SUCCESSFUL->value,
                'message' => 'Invoice marked as paid successfully',
            ]);
        } else {
            return Response::json([
                'status' => TransactionStatus::FAILED->value,
                'message' => 'The transaction provided is not valid',
            ]);
        }
    }
}
