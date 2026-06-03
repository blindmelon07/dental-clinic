<?php

namespace App\Services;

use App\Models\PatientCertificate;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class CertificateGenerator
{
    public static function generate(PatientCertificate $cert): string
    {
        $template = $cert->templatePath();

        if (! file_exists($template)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        $processor = new TemplateProcessor($template);
        $patient   = $cert->patient;
        $dentist   = $cert->issuedBy;

        // Common fields
        $processor->setValue('patient_name', $patient->full_name);
        $processor->setValue('age',          $patient->date_of_birth ? $patient->date_of_birth->age : '');
        $processor->setValue('date_treated', $cert->date_treated->format('F d, Y'));
        $processor->setValue('issue_date',   self::ordinalDate($cert->issue_date));
        $processor->setValue('findings',     $cert->findings ?? '');
        $processor->setValue('treatment_done', $cert->treatment_done ?? '');
        $processor->setValue('notes',        $cert->notes ?? '');

        // Gender pronoun
        $gender = strtolower($patient->gender?->value ?? 'other');
        $processor->setValue('pronoun_subject', $gender === 'female' ? 'She' : ($gender === 'male' ? 'He' : 'They'));
        $processor->setValue('pronoun_object',  $gender === 'female' ? 'Her' : ($gender === 'male' ? 'Him' : 'Them'));

        // Medical clearance fields
        if ($cert->type === 'medical_clearance') {
            $processor->setValue('date',               $cert->issue_date->format('F d, Y'));
            $processor->setValue('birthdate',          $cert->birthdate ? $cert->birthdate->format('F d, Y') : '');
            $processor->setValue('medical_conditions', $cert->medical_conditions ?? '');
        }

        // Save
        $dir      = storage_path('app/public/certificates');
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'certificates/' . strtolower(str_replace(' ', '_', $cert->typeLabel())) . '_' . $cert->certificate_number . '.docx';
        $fullPath = storage_path('app/public/' . $filename);

        $processor->saveAs($fullPath);

        $cert->update(['file_path' => $filename, 'generated_at' => now()]);

        return $filename;
    }

    private static function ordinalDate(\Carbon\Carbon $date): string
    {
        $day = $date->day;
        $suffix = match (true) {
            $day % 100 >= 11 && $day % 100 <= 13 => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default         => 'th',
        };
        return $day . $suffix . ' day of ' . $date->format('F Y');
    }
}
