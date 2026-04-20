<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'clinic_id', 'service_category_id', 'name', 'slug', 'description',
        'price', 'duration_minutes', 'requires_xray', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'requires_xray'  => 'boolean',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
