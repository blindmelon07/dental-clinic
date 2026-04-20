<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dentist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'clinic_id', 'license_number', 'specialization',
        'bio', 'consultation_fee', 'consultation_duration', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee'      => 'decimal:2',
            'consultation_duration' => 'integer',
            'is_active'             => 'boolean',
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

    public function schedules(): HasMany
    {
        return $this->hasMany(DentistSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dentalRecords(): HasMany
    {
        return $this->hasMany(DentalRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return "Dr. {$this->user->name}";
    }

    public function isAvailableOn(string $date, string $time): bool
    {
        $dayOfWeek = (int) date('N', strtotime($date));

        $schedule = $this->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->first();

        if (! $schedule) {
            return false;
        }

        return ! $this->appointments()
            ->where('appointment_date', $date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();
    }
}
