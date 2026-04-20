<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'clinic_id', 'patient_number', 'first_name', 'last_name',
        'middle_name', 'date_of_birth', 'gender', 'blood_type', 'phone',
        'email', 'address', 'city', 'emergency_contact_name',
        'emergency_contact_phone', 'emergency_contact_relation',
        'allergies', 'medical_conditions', 'current_medications', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender'        => Gender::class,
            'is_active'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dentalRecords(): HasMany
    {
        return $this->hasMany(DentalRecord::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }
}
