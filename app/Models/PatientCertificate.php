<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCertificate extends Model
{
    protected $fillable = [
        'patient_id', 'issued_by', 'type', 'certificate_number',
        'date_treated', 'issue_date', 'findings', 'treatment_done', 'notes',
        'birthdate', 'medical_conditions',
        'treatment_cleaning', 'treatment_xray', 'treatment_anesthetic',
        'treatment_extraction', 'treatment_root_canal', 'treatment_fillings', 'treatment_other',
        'file_path', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'date_treated'         => 'date',
            'issue_date'           => 'date',
            'birthdate'            => 'date',
            'generated_at'         => 'datetime',
            'treatment_cleaning'   => 'boolean',
            'treatment_xray'       => 'boolean',
            'treatment_anesthetic' => 'boolean',
            'treatment_extraction' => 'boolean',
            'treatment_root_canal' => 'boolean',
            'treatment_fillings'   => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'certification'     => 'Certification',
            'dental_clearance'  => 'Dental Clearance',
            'medical_clearance' => 'Medical Clearance',
            default             => ucfirst($this->type),
        };
    }

    public function templatePath(): string
    {
        return match ($this->type) {
            'certification'     => storage_path('app/templates/certification_template.docx'),
            'dental_clearance'  => storage_path('app/templates/dental_clearance_template.docx'),
            'medical_clearance' => storage_path('app/templates/medical_clearance_template.docx'),
            default             => '',
        };
    }

    public static function generateNumber(): string
    {
        return 'CERT-' . date('Ymd') . '-' . str_pad(
            static::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );
    }
}
