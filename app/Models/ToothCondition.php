<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToothCondition extends Model
{
    use Auditable, HasFactory;

    public const GROUPS = ['Condition', 'Restoration & Prosthetics', 'Surgery'];

    protected $fillable = [
        'code', 'label', 'color', 'group', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * All active conditions keyed by code, e.g. ['D' => ToothCondition, ...],
     * for quick lookups when rendering/painting the tooth chart.
     */
    public static function activeKeyedByCode()
    {
        return static::active()->ordered()->get()->keyBy('code');
    }
}
