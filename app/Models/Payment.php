<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'invoice_id', 'patient_id', 'amount',
        'payment_method', 'reference_number', 'notes', 'paid_at',
    ];

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
