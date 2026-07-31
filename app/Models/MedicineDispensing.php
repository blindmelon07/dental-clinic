<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineDispensing extends Model
{
    use Auditable;

    protected $fillable = [
        'medicine_id', 'patient_id', 'prescription_id',
        'dispensed_by', 'quantity', 'unit_price',
        'dispensed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
            'unit_price'   => 'decimal:2',
            'quantity'     => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Deduct stock when a dispensing is created
        static::created(function (MedicineDispensing $dispensing) {
            $dispensing->medicine()->decrement('current_stock', $dispensing->quantity);
        });

        // Restore stock when a dispensing is deleted
        static::deleted(function (MedicineDispensing $dispensing) {
            $dispensing->medicine()->increment('current_stock', $dispensing->quantity);
        });
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getDispensedByLabelAttribute(): string
    {
        return $this->dispensedBy?->role_and_name_label ?? '—';
    }

    public function auditableLabel(): string
    {
        return trim(($this->medicine?->display_name ?? 'Medicine') . ' to ' . ($this->patient?->full_name ?? 'patient'));
    }
}
