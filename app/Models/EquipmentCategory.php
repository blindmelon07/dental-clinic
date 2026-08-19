<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    use Auditable, HasFactory, HasUniqueSlug;

    protected $fillable = [
        'name', 'slug', 'description', 'color', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
