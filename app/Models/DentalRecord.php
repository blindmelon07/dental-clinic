<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'dentist_id', 'appointment_id', 'visit_date',
        'chief_complaint', 'diagnosis', 'treatment_plan', 'treatment_done',
        'tooth_chart', 'prescription', 'notes', 'next_visit_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'tooth_chart' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function xrays(): HasMany
    {
        return $this->hasMany(DentalXray::class);
    }
}
