<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PaymentPlan extends Model
{
    use Auditable;

    public const FREQUENCIES = [
        'weekly'   => 'Weekly',
        'biweekly' => 'Every 2 Weeks',
        'monthly'  => 'Monthly',
    ];

    /**
     * How many years out an installment schedule is allowed to stretch.
     */
    public const MAX_YEARS = 4;

    protected const INSTALLMENTS_PER_YEAR = [
        'weekly'   => 52,
        'biweekly' => 26,
        'monthly'  => 12,
    ];

    /**
     * The most installments a schedule can have at the given frequency without
     * running past MAX_YEARS from its start date.
     */
    public static function maxInstallmentCount(string $frequency): int
    {
        return (static::INSTALLMENTS_PER_YEAR[$frequency] ?? static::INSTALLMENTS_PER_YEAR['monthly']) * static::MAX_YEARS;
    }

    protected $fillable = [
        'dental_record_id', 'installment_count', 'frequency', 'start_date', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // total_amount always tracks the dental record's current balance due, so the
        // schedule stays correct if diagnosis/discount/partial payment change later.
        static::saving(function (PaymentPlan $plan) {
            $plan->total_amount = $plan->dentalRecord?->balance_due ?? $plan->total_amount ?? 0;
            $plan->installment_count = min(
                (int) $plan->installment_count,
                static::maxInstallmentCount($plan->frequency),
            );
        });

        static::saved(fn (PaymentPlan $plan) => $plan->syncInstallments());
    }

    public function dentalRecord(): BelongsTo
    {
        return $this->belongsTo(DentalRecord::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }

    public function auditableLabel(): string
    {
        return 'Payment Plan — ' . ($this->dentalRecord?->auditableLabel() ?? "Dental Record #{$this->dental_record_id}");
    }

    /**
     * Split total_amount evenly across installment_count, dumping any leftover
     * cents into the final installment so the rows always sum exactly to the total.
     *
     * @return array<int, array{installment_number: int, due_date: Carbon, amount: float}>
     */
    public function buildInstallments(): array
    {
        $count = max(1, (int) $this->installment_count);
        $base = round(((float) $this->total_amount) / $count, 2);
        $rows = [];
        $runningTotal = 0.0;

        for ($i = 1; $i <= $count; $i++) {
            $amount = $i === $count ? round(((float) $this->total_amount) - $runningTotal, 2) : $base;
            $runningTotal += $amount;

            $rows[] = [
                'installment_number' => $i,
                'due_date'           => $this->nthDueDate($i),
                'amount'             => $amount,
            ];
        }

        return $rows;
    }

    /**
     * Regenerate the installment rows from the current plan settings. Once any
     * installment has been paid, the schedule is left untouched — the form
     * disables the count/frequency/start_date fields at that point so this
     * should only ever run before collection has started.
     */
    public function syncInstallments(): void
    {
        if ($this->installments()->whereNotNull('payment_id')->exists()) {
            return;
        }

        $this->installments()->delete();

        foreach ($this->buildInstallments() as $row) {
            $this->installments()->create($row);
        }
    }

    protected function nthDueDate(int $n): Carbon
    {
        $date = $this->start_date->copy();

        return match ($this->frequency) {
            'weekly'   => $date->addWeeks($n - 1),
            'biweekly' => $date->addWeeks(($n - 1) * 2),
            default    => $date->addMonthsNoOverflow($n - 1),
        };
    }
}
