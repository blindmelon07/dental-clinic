<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class DentalRecord extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'dentist_id', 'appointment_id', 'visit_date',
        'chief_complaint', 'diagnosis', 'discount', 'treatment_plan', 'treatment_done',
        'tooth_chart', 'prescription', 'notes', 'next_visit_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'discount' => 'decimal:2',
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

    public function getSubtotalAttribute(): float
    {
        return Service::totalForDisplayNames($this->diagnosisNames());
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) ($this->discount ?? 0));
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

        $invoice = Invoice::createUnique([
            'clinic_id'        => $this->patient->clinic_id,
            'patient_id'       => $this->patient_id,
            'appointment_id'   => $this->appointment_id,
            'dental_record_id' => $this->id,
            'status'           => InvoiceStatus::Draft,
            'invoice_date'     => today(),
            'due_date'         => today()->addDays(7),
            'discount_amount'  => $this->discount ?? 0,
            'notes'            => 'Generated from dental record visit on ' . $this->visit_date->format('M d, Y') . '.',
        ]);

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

        return $invoice;
    }
}
