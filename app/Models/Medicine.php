<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'generic_name', 'brand', 'medicine_category_id', 'medicine_form_id',
        'strength', 'unit', 'current_stock', 'minimum_stock',
        'unit_price', 'expiry_date', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date'   => 'date',
            'is_active'     => 'boolean',
            'unit_price'    => 'decimal:2',
            'current_stock' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function dispensings(): HasMany
    {
        return $this->hasMany(MedicineDispensing::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(MedicineForm::class, 'medicine_form_id');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now(), false) >= -30 && !$this->isExpired();
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = [$this->name];
        if ($this->strength) $parts[] = $this->strength;
        if ($this->form)     $parts[] = '(' . $this->form->name . ')';
        return implode(' ', $parts);
    }
}
