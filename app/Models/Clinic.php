<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address', 'city',
        'state', 'zip', 'country', 'description', 'logo',
        'business_hours', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'is_active'      => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function dentists(): HasMany
    {
        return $this->hasMany(Dentist::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
