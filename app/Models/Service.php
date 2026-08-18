<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use Auditable, HasFactory, HasUniqueSlug;
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

    public static function totalForDisplayNames(array $displayNames): float
    {
        if (empty($displayNames)) {
            return 0;
        }

        $prices = static::where('is_active', true)
            ->with('category')
            ->get()
            ->mapWithKeys(fn (Service $service) => [$service->display_name => (float) $service->price]);

        // Sum per selected row (not per unique service) so picking the same
        // service more than once adds its price each time.
        return collect($displayNames)->sum(fn (string $name) => $prices->get($name, 0.0));
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
