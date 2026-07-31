<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'clinic_id', 'patient_number', 'first_name', 'last_name',
        'middle_name', 'nickname', 'date_of_birth', 'gender', 'blood_type',
        'religion', 'nationality', 'occupation',
        'phone', 'home_no', 'office_no', 'email', 'address', 'city',
        'dental_insurance', 'insurance_effective_date', 'referring_person', 'reason_for_consultation',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'guardian_name', 'guardian_occupation',
        'previous_dentist', 'last_dental_visit',
        'physician_name', 'physician_specialty', 'physician_office_address', 'physician_office_number',
        'in_good_health', 'under_medical_treatment', 'medical_treatment_condition',
        'serious_illness_or_surgery', 'serious_illness_details',
        'hospitalized', 'hospitalization_details',
        'takes_prescription_meds', 'uses_tobacco', 'uses_alcohol_drugs',
        'drug_allergies', 'drug_allergy_others', 'bleeding_time',
        'is_pregnant', 'is_nursing', 'taking_birth_control',
        'blood_pressure', 'medical_conditions_list',
        'allergies', 'medical_conditions', 'current_medications',
        'is_active', 'photo', 'next_cleaning_due',
        'consent_treatment_initials', 'consent_drugs_initials', 'consent_treatment_plan_initials',
        'consent_radiograph_initials', 'consent_removal_of_teeth_initials', 'consent_crowns_initials',
        'consent_endodontics_initials', 'consent_periodontal_initials', 'consent_fillings_initials',
        'consent_dentures_initials', 'consent_agreed', 'consent_patient_signature', 'consent_date',
        'consent_dentist_id', 'consent_dentist_name', 'consent_dentist_signature',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo) : null;
    }

    protected function casts(): array
    {
        return [
            'date_of_birth'             => 'date',
            'gender'                    => Gender::class,
            'is_active'                 => 'boolean',
            'next_cleaning_due'         => 'date',
            'insurance_effective_date'  => 'date',
            'last_dental_visit'         => 'date',
            'in_good_health'            => 'boolean',
            'under_medical_treatment'   => 'boolean',
            'serious_illness_or_surgery'=> 'boolean',
            'hospitalized'              => 'boolean',
            'takes_prescription_meds'   => 'boolean',
            'uses_tobacco'              => 'boolean',
            'uses_alcohol_drugs'        => 'boolean',
            'is_pregnant'               => 'boolean',
            'is_nursing'                => 'boolean',
            'taking_birth_control'      => 'boolean',
            'drug_allergies'            => 'array',
            'medical_conditions_list'   => 'array',
            'consent_agreed'            => 'boolean',
            'consent_date'              => 'date',
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

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class, 'consent_dentist_id');
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
