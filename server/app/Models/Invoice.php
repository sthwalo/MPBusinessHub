<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'business_id',
        'user_id',
        'payment_id',
        'package',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Helpers
    public function markAsPaid(Payment $payment = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
            'payment_id' => $payment ? $payment->id : null,
        ]);
    }

    public function getFormattedAmount()
    {
        return 'R' . number_format($this->total_amount, 2);
    }
}