<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name', 'is_cleaning', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_cleaning' => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }
}
