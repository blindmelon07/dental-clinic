<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_number', 'clinic_id', 'patient_id', 'dentist_id', 'service_id',
        'appointment_date', 'start_time', 'end_time', 'status', 'type',
        'chief_complaint', 'notes', 'cancellation_reason',
        'confirmed_at', 'cancelled_at', 'completed_at', 'booked_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status'           => AppointmentStatus::class,
            'type'             => AppointmentType::class,
            'confirmed_at'     => 'datetime',
            'cancelled_at'     => 'datetime',
            'completed_at'     => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function dentalRecord(): HasOne
    {
        return $this->hasOne(DentalRecord::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function confirm(): void
    {
        $this->update(['status' => AppointmentStatus::Confirmed, 'confirmed_at' => now()]);
    }

    public function cancel(string $reason = ''): void
    {
        $this->update([
            'status'              => AppointmentStatus::Cancelled,
            'cancellation_reason' => $reason,
            'cancelled_at'        => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update(['status' => AppointmentStatus::Completed, 'completed_at' => now()]);
    }

    public static function generateNumber(): string
    {
        return 'APT-' . date('Ymd') . '-' . str_pad(
            (static::whereDate('created_at', today())->count() + 1),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
