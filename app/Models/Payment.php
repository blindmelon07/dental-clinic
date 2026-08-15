<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use Auditable;

    protected $fillable = [
        'payment_number', 'invoice_id', 'patient_id', 'amount',
        'payment_method', 'reference_number', 'notes', 'paid_at', 'source',
    ];

    /**
     * Payments auto-recorded by DentalRecord::syncPartialPaymentToInvoice(). These
     * are the only payments that method is allowed to adjust or delete when a later
     * edit lowers partial_payment — manually recorded payments (installments, the
     * invoice's "Record Payment" action) are never touched.
     */
    public const SOURCE_DENTAL_RECORD_SYNC = 'dental_record_sync';

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'paid_at'        => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public static function generateNumber(): string
    {
        return 'PAY-' . date('Ymd') . '-' . str_pad(
            (static::whereDate('created_at', today())->count() + 1),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
