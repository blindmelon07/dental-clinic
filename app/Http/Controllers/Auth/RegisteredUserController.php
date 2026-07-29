<?php

namespace App\Http\Controllers\Auth;

use App\Filament\Resources\PatientResource;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'religionOptions'         => PatientResource::religionOptions(),
            'bloodTypeOptions'        => PatientResource::bloodTypeOptions(),
            'nationalityOptions'      => PatientResource::nationalityOptions(),
            'cityOptions'             => PatientResource::cityOptions(),
            'drugAllergyOptions'      => PatientResource::drugAllergyOptions(),
            'medicalConditionOptions' => PatientResource::medicalConditionOptions(),
            'dentists'                => $this->activeDentists(),
        ]);
    }

    protected function activeDentists()
    {
        $clinic = Clinic::where('is_active', true)->first() ?? Clinic::first();

        if (! $clinic) {
            return collect();
        }

        return Dentist::where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->with('user')
            ->get();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Account
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'phone'      => 'required|string|max:20',

            // Personal information
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'middle_name'     => 'nullable|string|max:100',
            'nickname'        => 'nullable|string|max:100',
            'date_of_birth'   => 'required|date|before:today',
            'gender'          => 'required|in:male,female,other',
            'religion'        => 'nullable|string|max:100',
            'nationality'     => 'nullable|string|max:100',
            'blood_type'      => 'nullable|string|max:5',
            'occupation'      => 'nullable|string|max:150',

            // Contact & address
            'address'   => 'required|string',
            'city'      => 'required|string|max:100',
            'home_no'   => 'nullable|string|max:30',
            'office_no' => 'nullable|string|max:30',

            // Insurance & referral
            'dental_insurance'         => 'nullable|string|max:150',
            'insurance_effective_date' => 'nullable|date',
            'referring_person'         => 'nullable|string|max:150',
            'reason_for_consultation'  => 'nullable|string',

            // For minors
            'guardian_name'       => 'nullable|string|max:150',
            'guardian_occupation' => 'nullable|string|max:150',

            // Emergency contact
            'emergency_contact_name'     => 'nullable|string|max:150',
            'emergency_contact_phone'    => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:100',

            // Dental history
            'previous_dentist'  => 'nullable|string|max:150',
            'last_dental_visit' => 'nullable|date',

            // Physician information
            'physician_name'           => 'nullable|string|max:150',
            'physician_specialty'      => 'nullable|string|max:150',
            'physician_office_number'  => 'nullable|string|max:30',
            'physician_office_address' => 'nullable|string',

            // Medical questionnaire
            'in_good_health'              => 'nullable|boolean',
            'under_medical_treatment'     => 'nullable|boolean',
            'medical_treatment_condition' => 'nullable|string',
            'serious_illness_or_surgery'  => 'nullable|boolean',
            'serious_illness_details'     => 'nullable|string',
            'hospitalized'                => 'nullable|boolean',
            'hospitalization_details'     => 'nullable|string',
            'takes_prescription_meds'     => 'nullable|boolean',
            'current_medications'         => 'nullable|string',
            'uses_tobacco'                => 'nullable|boolean',
            'uses_alcohol_drugs'          => 'nullable|boolean',
            'drug_allergies'              => 'nullable|array',
            'drug_allergies.*'            => 'string',
            'drug_allergy_others'         => 'nullable|string|max:150',
            'bleeding_time'               => 'nullable|string|max:100',
            'is_pregnant'                 => 'nullable|boolean',
            'is_nursing'                  => 'nullable|boolean',
            'taking_birth_control'        => 'nullable|boolean',
            'blood_pressure'              => 'nullable|string|max:50',
            'medical_conditions_list'     => 'nullable|array',
            'medical_conditions_list.*'   => 'string',
            'allergies'                   => 'nullable|string',
            'medical_conditions'          => 'nullable|string',

            // Informed consent
            'consent_treatment_initials'        => 'nullable|string|max:10',
            'consent_drugs_initials'            => 'nullable|string|max:10',
            'consent_treatment_plan_initials'   => 'nullable|string|max:10',
            'consent_radiograph_initials'       => 'nullable|string|max:10',
            'consent_removal_of_teeth_initials' => 'nullable|string|max:10',
            'consent_crowns_initials'           => 'nullable|string|max:10',
            'consent_endodontics_initials'      => 'nullable|string|max:10',
            'consent_periodontal_initials'      => 'nullable|string|max:10',
            'consent_fillings_initials'         => 'nullable|string|max:10',
            'consent_dentures_initials'         => 'nullable|string|max:10',
            'consent_agreed'                    => 'required|accepted',
            'consent_patient_signature'          => 'required|string',
            'consent_dentist_id'                 => 'nullable|exists:dentists,id',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'phone'     => $validated['phone'],
            'is_active' => false,
        ]);

        $user->assignRole('patient');

        $clinic = Clinic::where('is_active', true)->first() ?? Clinic::first();

        if ($clinic) {
            $user->update(['clinic_id' => $clinic->id]);

            $dentist = null;

            if (! empty($validated['consent_dentist_id'])) {
                $dentist = Dentist::where('clinic_id', $clinic->id)
                    ->where('is_active', true)
                    ->with('user')
                    ->find($validated['consent_dentist_id']);
            }

            if (! $dentist) {
                $dentist = Dentist::where('clinic_id', $clinic->id)
                    ->where('is_active', true)
                    ->with('user')
                    ->first();
            }

            Patient::create([
                ...array_diff_key($validated, array_flip(['name', 'password', 'consent_dentist_id'])),
                'user_id'        => $user->id,
                'clinic_id'      => $clinic->id,
                'patient_number' => 'PT-' . date('Ymd') . '-' . str_pad(
                    Patient::whereDate('created_at', today())->count() + 1,
                    4, '0', STR_PAD_LEFT
                ),
                'consent_date'             => today(),
                'consent_dentist_id'       => $dentist?->id,
                'consent_dentist_name'     => $dentist?->full_name,
                'consent_dentist_signature'=> $dentist?->signature,
            ]);
        }

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Registered event failed: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        Auth::login($user);

        return redirect()->route('patient.dashboard');
    }
}
