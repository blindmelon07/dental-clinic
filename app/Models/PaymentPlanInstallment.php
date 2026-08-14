<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentPlanInstallment extends Model
{
    protected $fillable = [
        'payment_plan_id', 'installment_number', 'due_date', 'amount', 'payment_id', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount'   => 'decimal:2',
            'paid_at'  => 'datetime',
        ];
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_id !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isPaid() && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isPaid()    => 'Paid',
            $this->isOverdue() => 'Overdue',
            default             => 'Upcoming',
        };
    }

    public function statusColor(): string
    {
        return match (true) {
            $this->isPaid()    => 'success',
            $this->isOverdue() => 'danger',
            default             => 'gray',
        };
    }
}
