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

        if ($this->partial_payment > 0 && $invoice->payments()->doesntExist()) {
            $invoice->payments()->create([
                'payment_number' => Payment::generateNumber(),
                'patient_id'     => $this->patient_id,
                'amount'         => min((float) $this->partial_payment, (float) $invoice->total),
                'payment_method' => PaymentMethod::Cash,
                'notes'          => 'Partial payment recorded at the time of the dental visit.',
                'paid_at'        => now(),
            ]);

            $invoice->recalculate();

            $invoice->update([
                'status'  => $invoice->fresh()->balance_due <= 0 ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid,
                'paid_at' => $invoice->fresh()->balance_due <= 0 ? now() : null,
            ]);
        }

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
}
