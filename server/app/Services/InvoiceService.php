<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class InvoiceService
{
    public function generateInvoice(Business $business, string $package, float $amount, string $description = null): Invoice
    {
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 3, '0', STR_PAD_LEFT);
        $issueDate = now();
        $dueDate = $issueDate->copy()->addDays(7);
        $taxAmount = $amount * 0.15; // 15% VAT

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'business_id' => $business->id,
            'user_id' => $business->user_id,
            'package' => $package,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $amount + $taxAmount,
            'status' => 'sent',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'notes' => $description,
        ]);
    }

    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        $payment = Payment::create(array_merge($paymentData, [
            'business_id' => $invoice->business_id,
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount,
            'status' => 'completed',
            'payment_date' => now(),
        ]));

        $invoice->markAsPaid($payment);

        return $payment;
    }
}