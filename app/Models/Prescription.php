<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number', 'clinic_id', 'patient_id', 'dentist_id',
        'dental_record_id', 'appointment_id', 'prescribed_date',
        'diagnosis', 'medications', 'notes', 'is_printed', 'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_date' => 'date',
            'medications'     => 'array',
            'is_printed'      => 'boolean',
            'printed_at'      => 'datetime',
        ];
    }

    public function clinic(): BelongsTo      { return $this->belongsTo(Clinic::class); }
    public function patient(): BelongsTo     { return $this->belongsTo(Patient::class); }
    public function dentist(): BelongsTo     { return $this->belongsTo(Dentist::class); }
    public function dentalRecord(): BelongsTo { return $this->belongsTo(DentalRecord::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }

    public static function generateNumber(): string
    {
        return 'RX-' . date('Ymd') . '-' . str_pad(
            (static::whereDate('created_at', today())->count() + 1),
            4, '0', STR_PAD_LEFT
        );
    }

    public function markPrinted(): void
    {
        $this->update(['is_printed' => true, 'printed_at' => now()]);
    }
}
