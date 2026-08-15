<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class DentalRecord extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'dentist_id', 'appointment_id', 'visit_date',
        'chief_complaint', 'diagnosis', 'discount', 'partial_payment', 'treatment_plan', 'treatment_done',
        'tooth_chart', 'prescription', 'notes', 'next_visit_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'discount' => 'decimal:2',
            'partial_payment' => 'decimal:2',
            'tooth_chart' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class);
    }

    public function auditableExcluded(): array
    {
        return ['tooth_chart'];
    }

    public function auditableLabel(): string
    {
        $visit = $this->visit_date?->format('M d, Y') ?? 'undated visit';

        return trim(($this->patient?->full_name ?? 'Dental Record') . ' — ' . $visit);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function xrays(): HasMany
    {
        return $this->hasMany(DentalXray::class);
    }

    public function oldTreatmentImages(): HasMany
    {
        return $this->hasMany(OldTreatmentImage::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentPlan(): HasOne
    {
        return $this->hasOne(PaymentPlan::class);
    }

    public function installments(): HasManyThrough
    {
        return $this->hasManyThrough(
            PaymentPlanInstallment::class,
            PaymentPlan::class,
            'dental_record_id',
            'payment_plan_id',
        );
    }

    public function getSubtotalAttribute(): float
    {
        return Service::totalForDisplayNames($this->diagnosisNames());
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) ($this->discount ?? 0));
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total - (float) ($this->partial_payment ?? 0));
    }

    public function diagnosisNames(): array
    {
        return filled($this->diagnosis) ? array_map('trim', explode(',', $this->diagnosis)) : [];
    }

    public function diagnosisServices(): \Illuminate\Support\Collection
    {
        $names = $this->diagnosisNames();

        return Service::where('is_active', true)
            ->with('category')
            ->get()
            ->filter(fn (Service $service) => in_array($service->display_name, $names, true));
    }

    public function createInvoice(): Invoice
    {
        $services = $this->diagnosisServices();

        if ($services->isEmpty()) {
            throw new RuntimeException('No active services match this diagnosis — cannot generate an invoice.');
        }

        $attributes = [
            'clinic_id'        => $this->patient->clinic_id,
            'patient_id'       => $this->patient_id,
            'appointment_id'   => $this->appointment_id,
            'dental_record_id' => $this->id,
            'status'           => InvoiceStatus::Draft,
            'invoice_date'     => today(),
            'due_date'         => today()->addDays(7),
            'discount_amount'  => $this->discount ?? 0,
            'notes'            => 'Generated from dental record visit on ' . $this->visit_date->format('M d, Y') . '.',
        ];

        // dental_record_id is unique on invoices even across soft-deleted rows, so a
        // previously-deleted invoice for this record still occupies the slot. Restore
        // and reuse it instead of inserting a new row, which would violate that
        // constraint.
        $existing = Invoice::withTrashed()->where('dental_record_id', $this->id)->first();

        if ($existing?->trashed()) {
            $existing->restore();
            $existing->items()->delete();
            $existing->update(array_merge($attributes, ['invoice_number' => Invoice::generateNumber()]));
            $invoice = $existing;
        } elseif ($existing) {
            throw new RuntimeException('An invoice already exists for this dental record.');
        } else {
            $invoice = Invoice::createUnique($attributes);
        }

        foreach ($services as $service) {
            $invoice->items()->create([
                'service_id'  => $service->id,
                'description' => $service->display_name,
                'quantity'    => 1,
                'unit_price'  => $service->price,
                'total'       => $service->price,
            ]);
        }

        $invoice->recalculate();

        $this->syncPartialPaymentToInvoice($invoice);

        return $invoice;
    }

    /**
     * The active invoice for this visit, generating one from the diagnosis if it
     * doesn't exist yet. Used when collecting an installment payment, which needs
     * an invoice to attach the Payment record to.
     */
    public function getOrCreateInvoice(): Invoice
    {
        return $this->invoices()->first() ?? $this->createInvoice();
    }

    /**
     * Reconciles this record's `partial_payment` figure against the given invoice
     * (or its existing invoice, if any). Used both when an invoice is first
     * generated and whenever the dental record is edited afterward, so changing
     * `partial_payment` on a later visit edit actually shows up on the invoice
     * instead of silently going stale.
     *
     * Only payments this method itself created (source = dental_record_sync) are
     * ever adjusted or removed here — manually recorded payments (installments,
     * the invoice's "Record Payment" action) are a ledger of what was actually
     * collected and are never rewritten automatically.
     */
    public function syncPartialPaymentToInvoice(?Invoice $invoice = null): void
    {
        $invoice ??= $this->invoices()->first();

        if (! $invoice) {
            return;
        }

        $autoPayments = $invoice->payments()
            ->where('source', Payment::SOURCE_DENTAL_RECORD_SYNC)
            ->orderBy('paid_at')
            ->get();

        $manualPaid = (float) $invoice->payments()
            ->where('source', '!=', Payment::SOURCE_DENTAL_RECORD_SYNC)
            ->sum('amount');
        $autoPaid = (float) $autoPayments->sum('amount');

        $target = min((float) ($this->partial_payment ?? 0), (float) $invoice->total);
        $targetAuto = round(max(0, $target - $manualPaid), 2);
        $difference = round($targetAuto - $autoPaid, 2);

        if ($difference > 0) {
            $invoice->payments()->create([
                'payment_number' => Payment::generateNumber(),
                'patient_id'     => $this->patient_id,
                'amount'         => $difference,
                'payment_method' => PaymentMethod::Cash,
                'source'         => Payment::SOURCE_DENTAL_RECORD_SYNC,
                'notes'          => $autoPaid > 0
                    ? 'Additional partial payment recorded from a dental record update.'
                    : 'Partial payment recorded at the time of the dental visit.',
                'paid_at'        => now(),
            ]);
        } elseif ($difference < 0) {
            $toRemove = abs($difference);

            // Walk the auto-synced payments newest-first, deleting or shrinking
            // them until the reduction is fully absorbed.
            foreach ($autoPayments->sortByDesc('paid_at') as $payment) {
                if ($toRemove <= 0) {
                    break;
                }

                if ((float) $payment->amount <= $toRemove) {
                    $toRemove = round($toRemove - (float) $payment->amount, 2);
                    $payment->delete();
                } else {
                    $payment->update(['amount' => round((float) $payment->amount - $toRemove, 2)]);
                    $toRemove = 0;
                }
            }
        } else {
            return;
        }

        $invoice->recalculate();

        $invoice->update([
            'status'  => $invoice->fresh()->balance_due <= 0 ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid,
            'paid_at' => $invoice->fresh()->balance_due <= 0 ? now() : null,
        ]);
    }
}
