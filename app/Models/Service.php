<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use Auditable, HasFactory;
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

    public function getDisplayNameAttribute(): string
    {
        $categoryName = $this->category?->name;
        return $categoryName ? "{$categoryName} – {$this->name}" : $this->name;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public static function totalForDisplayNames(array $displayNames): float
    {
        if (empty($displayNames)) {
            return 0;
        }

        return static::where('is_active', true)
            ->with('category')
            ->get()
            ->filter(fn (Service $service) => in_array($service->display_name, $displayNames, true))
            ->sum(fn (Service $service) => (float) $service->price);
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
