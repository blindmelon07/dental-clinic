<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    use Auditable;

    protected $table = 'equipment';

    protected $fillable = [
        'name', 'equipment_category_id', 'serial_number', 'status', 'quantity', 'location',
        'supplier', 'purchase_date', 'purchase_cost', 'warranty_expires_at',
        'last_maintenance_date', 'next_maintenance_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'                 => EquipmentStatus::class,
            'quantity'               => 'integer',
            'purchase_date'          => 'date',
            'purchase_cost'          => 'decimal:2',
            'warranty_expires_at'    => 'date',
            'last_maintenance_date'  => 'date',
            'next_maintenance_date'  => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance_date !== null && $this->next_maintenance_date->isPast();
    }

    public function isMaintenanceDueSoon(): bool
    {
        return $this->next_maintenance_date !== null
            && ! $this->isMaintenanceDue()
            && $this->next_maintenance_date->diffInDays(now(), false) >= -14;
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expires_at !== null && $this->warranty_expires_at->isFuture();
    }
}
