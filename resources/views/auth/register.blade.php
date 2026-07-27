<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — Gonzales Dental Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&family=Noto+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="min-h-dvh font-sans antialiased bg-gradient-to-br from-teal-50 via-cyan-50 to-sky-100 py-8 px-4">

    <div class="w-full max-w-4xl mx-auto">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('login') }}" class="inline-flex flex-col items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded-2xl p-2">
                <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}"
                     alt="Gonzales Dental Clinic"
                     class="h-16 w-auto object-contain">
                <p class="text-slate-500 text-sm">Patient Registration</p>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <h2 class="font-heading text-xl font-semibold text-slate-900 mb-2">Create your patient account</h2>
            <p class="text-sm text-slate-500 mb-6">
                Fill in the form below to register. All fields marked <span class="text-red-500">*</span> are required.
                The rest helps our staff prepare for your first visit — you can also complete or update it later from your patient dashboard.
            </p>

            <form method="POST" action="{{ route('register') }}" novalidate id="registration-form">
                @csrf

                @if ($errors->any())
                    <div role="alert" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-semibold text-red-800 mb-1">Please fix the following errors:</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div id="registration-wizard" data-has-errors="{{ $errors->any() ? '1' : '0' }}">

                    {{-- Progress indicator --}}
                    <div class="mb-6">
                        <p id="wizard-progress-text" class="text-sm font-semibold text-cyan-700 mb-2"></p>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div id="wizard-progress-bar" class="h-full bg-cyan-600 transition-all duration-300 ease-out" style="width: 0%"></div>
                        </div>
                    </div>

                    {{-- Step 1 --}}
                    <section class="wizard-step" data-step-title="Account Details">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Account Details
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Full Name <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                                           autocomplete="name"
                                           class="input-field {{ $errors->has('name') ? 'border-red-400' : '' }}">
                                    @error('name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Email Address <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                           autocomplete="email"
                                           class="input-field {{ $errors->has('email') ? 'border-red-400' : '' }}">
                                    @error('email') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Password <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="password" type="password" name="password" required
                                           autocomplete="new-password"
                                           class="input-field {{ $errors->has('password') ? 'border-red-400' : '' }}">
                                    @error('password') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Confirm Password <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" required
                                           autocomplete="new-password"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Cell / Mobile No. <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                                           autocomplete="tel" placeholder="0917 123 4567"
                                           inputmode="numeric" maxlength="13"
                                           class="input-field {{ $errors->has('phone') ? 'border-red-400' : '' }}">
                                    @error('phone') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- Step 2 --}}
                    <section class="wizard-step" data-step-title="Personal Information">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Personal Information
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Last Name <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required
                                           autocomplete="family-name"
                                           class="input-field {{ $errors->has('last_name') ? 'border-red-400' : '' }}">
                                    @error('last_name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        First Name <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required
                                           autocomplete="given-name"
                                           class="input-field {{ $errors->has('first_name') ? 'border-red-400' : '' }}">
                                    @error('first_name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-slate-700 mb-1.5">Middle Name</label>
                                    <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Date of Birth <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                                           autocomplete="bday"
                                           class="input-field {{ $errors->has('date_of_birth') ? 'border-red-400' : '' }}">
                                    @error('date_of_birth') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Sex <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <select id="gender" name="gender" required
                                            class="input-field {{ $errors->has('gender') ? 'border-red-400' : '' }}">
                                        <option value="">Select sex</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                                    </select>
                                    @error('gender') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="nickname" class="block text-sm font-medium text-slate-700 mb-1.5">Nickname</label>
                                    <input id="nickname" type="text" name="nickname" value="{{ old('nickname') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="religion" class="block text-sm font-medium text-slate-700 mb-1.5">Religion</label>
                                    <select id="religion" name="religion" class="input-field">
                                        <option value="">Select religion</option>
                                        @foreach ($religionOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('religion') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="nationality" class="block text-sm font-medium text-slate-700 mb-1.5">Nationality</label>
                                    <select id="nationality" name="nationality" class="input-field">
                                        <option value="">Select nationality</option>
                                        @foreach ($nationalityOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('nationality') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="blood_type" class="block text-sm font-medium text-slate-700 mb-1.5">Blood Type</label>
                                    <select id="blood_type" name="blood_type" class="input-field">
                                        <option value="">Select blood type</option>
                                        @foreach ($bloodTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('blood_type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="occupation" class="block text-sm font-medium text-slate-700 mb-1.5">Occupation</label>
                                    <input id="occupation" type="text" name="occupation" value="{{ old('occupation') }}"
                                           class="input-field">
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- Step 3 --}}
                    <section class="wizard-step" data-step-title="Contact, Insurance & Emergency">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Contact & Address
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Home Address <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <textarea id="address" name="address" rows="2" required
                                              autocomplete="street-address"
                                              class="input-field {{ $errors->has('address') ? 'border-red-400' : '' }}">{{ old('address') }}</textarea>
                                    @error('address') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="city" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        City <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <select id="city" name="city" required
                                            class="input-field {{ $errors->has('city') ? 'border-red-400' : '' }}">
                                        <option value="">Select city</option>
                                        @foreach ($cityOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('city') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('city') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="home_no" class="block text-sm font-medium text-slate-700 mb-1.5">Home No.</label>
                                    <input id="home_no" type="tel" name="home_no" value="{{ old('home_no') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="office_no" class="block text-sm font-medium text-slate-700 mb-1.5">Office No.</label>
                                    <input id="office_no" type="tel" name="office_no" value="{{ old('office_no') }}"
                                           class="input-field">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Insurance & Referral
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="dental_insurance" class="block text-sm font-medium text-slate-700 mb-1.5">Dental Insurance</label>
                                    <input id="dental_insurance" type="text" name="dental_insurance" value="{{ old('dental_insurance') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="insurance_effective_date" class="block text-sm font-medium text-slate-700 mb-1.5">Effective Date</label>
                                    <input id="insurance_effective_date" type="date" name="insurance_effective_date" value="{{ old('insurance_effective_date') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="referring_person" class="block text-sm font-medium text-slate-700 mb-1.5">Referred By</label>
                                    <input id="referring_person" type="text" name="referring_person" value="{{ old('referring_person') }}"
                                           class="input-field">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="reason_for_consultation" class="block text-sm font-medium text-slate-700 mb-1.5">Reason for Dental Consultation</label>
                                    <textarea id="reason_for_consultation" name="reason_for_consultation" rows="2"
                                              class="input-field">{{ old('reason_for_consultation') }}</textarea>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                For Minors <span class="text-slate-400 font-normal text-sm">(if the patient is under 18)</span>
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="guardian_name" class="block text-sm font-medium text-slate-700 mb-1.5">Parent / Guardian's Name</label>
                                    <input id="guardian_name" type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="guardian_occupation" class="block text-sm font-medium text-slate-700 mb-1.5">Guardian's Occupation</label>
                                    <input id="guardian_occupation" type="text" name="guardian_occupation" value="{{ old('guardian_occupation') }}"
                                           class="input-field">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Emergency Contact
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                                    <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="emergency_contact_phone" class="block text-sm font-medium text-slate-700 mb-1.5">Phone</label>
                                    <input id="emergency_contact_phone" type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="emergency_contact_relation" class="block text-sm font-medium text-slate-700 mb-1.5">Relation</label>
                                    <input id="emergency_contact_relation" type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation') }}"
                                           class="input-field">
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- Step 4 --}}
                    <section class="wizard-step" data-step-title="Dental & Physician History">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Dental History
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="previous_dentist" class="block text-sm font-medium text-slate-700 mb-1.5">Previous Dentist: Dr.</label>
                                    <input id="previous_dentist" type="text" name="previous_dentist" value="{{ old('previous_dentist') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="last_dental_visit" class="block text-sm font-medium text-slate-700 mb-1.5">Last Dental Visit</label>
                                    <input id="last_dental_visit" type="date" name="last_dental_visit" value="{{ old('last_dental_visit') }}"
                                           class="input-field">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Physician Information
                            </legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                <div>
                                    <label for="physician_name" class="block text-sm font-medium text-slate-700 mb-1.5">Name of Physician: Dr.</label>
                                    <input id="physician_name" type="text" name="physician_name" value="{{ old('physician_name') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="physician_specialty" class="block text-sm font-medium text-slate-700 mb-1.5">Specialty</label>
                                    <input id="physician_specialty" type="text" name="physician_specialty" value="{{ old('physician_specialty') }}"
                                           class="input-field">
                                </div>
                                <div>
                                    <label for="physician_office_number" class="block text-sm font-medium text-slate-700 mb-1.5">Office Number</label>
                                    <input id="physician_office_number" type="tel" name="physician_office_number" value="{{ old('physician_office_number') }}"
                                           class="input-field">
                                </div>
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label for="physician_office_address" class="block text-sm font-medium text-slate-700 mb-1.5">Office Address</label>
                                    <textarea id="physician_office_address" name="physician_office_address" rows="2"
                                              class="input-field">{{ old('physician_office_address') }}</textarea>
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- Step 5 --}}
                    <section class="wizard-step" data-step-title="Medical Questionnaire">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Medical Questionnaire
                            </legend>

                            <div class="space-y-5">
                                <div>
                                    <x-bool-field name="in_good_health" label="1. Are you in good health?" />
                                </div>

                                <div>
                                    <x-bool-field name="under_medical_treatment" label="2. Are you under medical treatment now?" />
                                    <input type="text" name="medical_treatment_condition" value="{{ old('medical_treatment_condition') }}"
                                           placeholder="If yes, what is the condition being treated?"
                                           class="input-field mt-2">
                                </div>

                                <div>
                                    <x-bool-field name="serious_illness_or_surgery" label="3. Have you ever had a serious illness or surgical operation?" />
                                    <input type="text" name="serious_illness_details" value="{{ old('serious_illness_details') }}"
                                           placeholder="If yes, what illness or operation?"
                                           class="input-field mt-2">
                                </div>

                                <div>
                                    <x-bool-field name="hospitalized" label="4. Have you ever been hospitalized?" />
                                    <input type="text" name="hospitalization_details" value="{{ old('hospitalization_details') }}"
                                           placeholder="If yes, when and why?"
                                           class="input-field mt-2">
                                </div>

                                <div>
                                    <x-bool-field name="takes_prescription_meds" label="5. Are you taking any prescription / non-prescription medication?" />
                                    <textarea name="current_medications" rows="2" placeholder="If yes, please specify"
                                              class="input-field mt-2">{{ old('current_medications') }}</textarea>
                                </div>

                                <x-bool-field name="uses_tobacco" label="6. Do you use tobacco products?" />
                                <x-bool-field name="uses_alcohol_drugs" label="7. Do you use alcohol, cocaine or other dangerous drugs?" />

                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">8. Are you allergic to any of the following?</p>
                                    <x-checkbox-grid name="drug_allergies" :options="$drugAllergyOptions" />
                                    <input type="text" name="drug_allergy_others" value="{{ old('drug_allergy_others') }}"
                                           placeholder="Others (please specify)"
                                           class="input-field mt-3">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label for="bleeding_time" class="block text-sm font-medium text-slate-700 mb-1.5">9. Bleeding Time</label>
                                        <input id="bleeding_time" type="text" name="bleeding_time" value="{{ old('bleeding_time') }}"
                                               class="input-field">
                                    </div>
                                    <div>
                                        <label for="blood_pressure" class="block text-sm font-medium text-slate-700 mb-1.5">12. Blood Pressure</label>
                                        <input id="blood_pressure" type="text" name="blood_pressure" value="{{ old('blood_pressure') }}"
                                               class="input-field">
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">10. For Women Only</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6">
                                        <x-bool-field name="is_pregnant" label="Are you pregnant?" />
                                        <x-bool-field name="is_nursing" label="Are you nursing?" />
                                        <x-bool-field name="taking_birth_control" label="Taking birth control pills?" />
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">13. Do you have or have you had any of the following?</p>
                                    <x-checkbox-grid name="medical_conditions_list" :options="$medicalConditionOptions" />
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Additional Notes
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="allergies" class="block text-sm font-medium text-slate-700 mb-1.5">Other Allergies</label>
                                    <textarea id="allergies" name="allergies" rows="3" class="input-field">{{ old('allergies') }}</textarea>
                                </div>
                                <div>
                                    <label for="medical_conditions" class="block text-sm font-medium text-slate-700 mb-1.5">Other Medical Conditions</label>
                                    <textarea id="medical_conditions" name="medical_conditions" rows="3" class="input-field">{{ old('medical_conditions') }}</textarea>
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- Step 6 --}}
                    <section class="wizard-step" data-step-title="Informed Consent">
                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Informed Consent
                            </legend>
                            <p class="text-sm text-slate-500 mb-4">Please read each statement below and write your initials to confirm you understand it.</p>

                            <div class="space-y-4">
                                <x-consent-clause name="consent_treatment_initials" title="TREATMENT TO BE DONE"
                                    description="I understand and consent to have any treatment done by the dentist after the procedure, the risks & benefits & cost have been fully explained. These treatments include, but are not limited to: x rays, cleanings, periodontal treatments, fillings, crowns, bridges, all types of extraction, root canals, &/or dentures, local anesthetics & surgical cases." />

                                <x-consent-clause name="consent_drugs_initials" title="DRUGS & MEDICATIONS"
                                    description="I understand that antibiotics, analgesics & other medications can cause allergic reactions like redness & swelling of tissues, pain, itching, vomiting, &/or anaphylactic shock." />

                                <x-consent-clause name="consent_treatment_plan_initials" title="CHANGES IN TREATMENT PLAN"
                                    description="I understand that during treatment it may be necessary to change/add procedures because of conditions found while working on the teeth that was not discovered during examination. For example, root canal therapy may be needed following routine restorative procedures. I give my permission to the dentist to make any/all changes and additions as necessary w/ my responsibility to pay all the costs agreed." />

                                <x-consent-clause name="consent_radiograph_initials" title="RADIOGRAPH"
                                    description="I understand that an x-ray shot or a radiograph maybe necessary as part of diagnostic aid to come up with tentative diagnosis at my dental problem and to make a good treatment plan, but this will not give me a 100% assurance for the accuracy of the treatment since all dental treatments are subject to unpredictable complications that later on may lead to sudden change of treatment plan and subject to new charges." />

                                <x-consent-clause name="consent_removal_of_teeth_initials" title="REMOVAL OF TEETH"
                                    description="I understand that alternatives to tooth removal (root canal therapy, crowns & periodontal surgery, etc.) & I completely understand these alternatives, including their risk & benefits prior to authorizing the dentist to remove teeth & any other structures necessary for reasons above. I understand that removing teeth does not always remove all the infections, if present, & it may be necessary to have further treatment. I understand the risk involved in having teeth removed, such as: pain, swelling, spread of infection, dry socket, fractured jaw, loss of feeling on the teeth, lips, tongue & surrounding tissue that can last for an indefinite period of time. I understand that I may need further treatment under a specialist if complications arise during or following treatment." />

                                <x-consent-clause name="consent_crowns_initials" title="CROWNS (CAPS) & BRIDGES"
                                    description="Preparing a tooth may irritate the nerve tissue in the center of the tooth, leaving the tooth extra sensitive to heat, cold & pressure. Treating such irritation may involve using special toothpastes, mouth rinses or root canal therapy. I understand that sometimes it is not possible to match the color of natural teeth exactly with artificial teeth. I further understand that I may be wearing temporary crowns, which may come off easily & that I must be careful to ensure that they are kept on until the permanent crowns are delivered. It is my responsibility to return for permanent cementation within 20 days from tooth preparation, as excessive days delay may allow for tooth movement, which may necessitate a remake of the crown, bridge/cap. I understand there will be additional charges for remakes due to my delaying of permanent cementation, & I realize that final opportunity to make changes in my new crown, bridges or cap (including shape, fit, size & color) will be before permanent cementation." />

                                <x-consent-clause name="consent_endodontics_initials" title="ENDODONTICS (ROOT CANAL)"
                                    description="I understand there is no guarantee that a root canal treatment will save a tooth & that complications can occur from the treatment & that occasionally root canal filling materials may extend through the tooth which does not necessarily affect the success of the treatment. I understand that endodontic files & drills are very fine instruments & stresses vented in their manufacture & calcifications present in teeth can cause them to break during use. I understand that referral to the endodontist for additional treatments may be necessary following any root canal treatment & I agree that I am responsible for any additional cost for treatment performed by the endodontist. I understand that a tooth may require removal in spite of all efforts to save it." />

                                <x-consent-clause name="consent_periodontal_initials" title="PERIODONTAL DISEASE"
                                    description="I understand that periodontal disease is a serious condition causing gum & bone inflammation &/or loss & that can lead eventually to the loss of my teeth. I understand the alternative treatment plans to correct periodontal disease, including gum surgery tooth extractions with or without replacement. I understand that undertaking any dental procedures may have future adverse effect on my periodontal conditions." />

                                <x-consent-clause name="consent_fillings_initials" title="FILLINGS"
                                    description="I understand that care must be exercised in chewing on fillings, especially during the first 24 hours to avoid breakage. I understand that a more extensive filling or a crown may be required, as additional decay or fracture may become evident after initial excavation. I understand that significant sensitivity is a common, but usually temporary, after-effect of a new placement of filling. I further understand that filling a tooth may irritate the nerve tissue creating sensitivity & treating such sensitivity could require root canal therapy or extractions." />

                                <x-consent-clause name="consent_dentures_initials" title="DENTURES"
                                    description="I understand that wearing of dentures can be difficult. Sore spots, altered speech & difficulty in eating are common problems. Immediate dentures (placement of denture immediately after extractions) may be painful. Immediate dentures may require considerable adjusting & several relines. I understand that it is my responsibility to return for delivery of dentures. I understand that failure to keep my delivery appointment may result in poorly fitted dentures. If a remake is required due to my delays of more than 30 days, there will be additional charges. A permanent reline will be needed later, which is not included in the initial fee. I understand that all adjustment or alterations of any kind after this initial period is subject to charges." />
                            </div>
                        </fieldset>

                        <fieldset class="mb-8">
                            <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                                Authorization & Signature
                            </legend>
                            <p class="text-sm text-slate-500 leading-relaxed mb-4">
                                I understand that dentistry is not an exact science and that no dentist can properly guarantee accurate results all the time. I hereby authorize any of the doctors/dental auxiliaries to proceed with & perform the dental restorations & treatments as explained to me. I understand that these are subject to modification depending on undiagnosable circumstances that may arise during the course of treatment. I understand that regarding any dental insurance coverage I may have, I am responsible for payment of dental fees. I agree to pay any attorney's fees, collection fee, or court costs that may be incurred to satisfy any obligation to this office. All treatment were properly explained to me & any untoward circumstances that may arise during the procedure, the attending dentist will not be held liable since it is my free will, with full trust & confidence in him/her, to undergo dental treatment under his/her care.
                            </p>

                            <x-bool-field name="consent_agreed" label="Patient / Parent / Guardian agrees to the above informed consent" :required="true" />

                            <div class="mt-4">
                                <x-signature-field name="consent_patient_signature" label="Patient / Parent / Guardian Signature" :required="true" />
                            </div>
                        </fieldset>
                    </section>

                    {{-- Wizard navigation --}}
                    <div class="flex items-center justify-between mt-2 pt-4 border-t border-slate-100">
                        <button type="button" id="wizard-back"
                                class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors invisible">
                            Back
                        </button>
                        <div class="flex gap-3">
                            <button type="button" id="wizard-next"
                                    class="px-6 py-2.5 rounded-xl bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 transition-colors shadow-sm">
                                Next
                            </button>
                            <button type="submit" id="wizard-submit"
                                    class="hidden px-6 py-2.5 rounded-xl bg-cyan-600 text-white text-sm font-semibold hover:bg-cyan-700 transition-colors shadow-sm">
                                Create Patient Account
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-cyan-600 font-semibold hover:text-cyan-700 hover:underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var wizard = document.getElementById('registration-wizard');
            if (!wizard || wizard.dataset.hasErrors === '1') return;

            var steps = Array.prototype.slice.call(wizard.querySelectorAll('.wizard-step'));
            var total = steps.length;
            var current = 0;

            var progressText = document.getElementById('wizard-progress-text');
            var progressBar = document.getElementById('wizard-progress-bar');
            var backBtn = document.getElementById('wizard-back');
            var nextBtn = document.getElementById('wizard-next');
            var submitBtn = document.getElementById('wizard-submit');

            function render(scroll) {
                steps.forEach(function (step, i) {
                    step.classList.toggle('hidden', i !== current);
                });
                progressText.textContent = 'Step ' + (current + 1) + ' of ' + total + ': ' + steps[current].dataset.stepTitle;
                progressBar.style.width = ((current + 1) / total * 100) + '%';
                backBtn.classList.toggle('invisible', current === 0);
                nextBtn.classList.toggle('hidden', current === total - 1);
                submitBtn.classList.toggle('hidden', current !== total - 1);

                window.dispatchEvent(new CustomEvent('wizard:step-shown', { detail: { step: steps[current] } }));

                if (scroll) {
                    wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function validateCurrentStep() {
                var fields = steps[current].querySelectorAll('input, select, textarea');
                for (var i = 0; i < fields.length; i++) {
                    if (!fields[i].checkValidity()) {
                        fields[i].reportValidity();
                        return false;
                    }
                }

                var requiredSignatures = steps[current].querySelectorAll('.signature-field[data-required="1"]');
                for (var j = 0; j < requiredSignatures.length; j++) {
                    var sigField = requiredSignatures[j];
                    var hiddenInput = sigField.querySelector('[data-hidden-input]');
                    var sigError = sigField.querySelector('[data-sig-error]');
                    var isEmpty = !hiddenInput || !hiddenInput.value;
                    if (sigError) {
                        sigError.classList.toggle('hidden', !isEmpty);
                    }
                    if (isEmpty) {
                        sigField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                }

                return true;
            }

            backBtn.addEventListener('click', function () {
                if (current > 0) {
                    current--;
                    render(true);
                }
            });

            nextBtn.addEventListener('click', function () {
                if (validateCurrentStep()) {
                    current = Math.min(current + 1, total - 1);
                    render(true);
                }
            });

            render(false);
        })();

        (function () {
            var phone = document.getElementById('phone');
            if (!phone) return;

            phone.addEventListener('input', function () {
                var digits = phone.value.replace(/\D/g, '').slice(0, 11);
                var formatted = digits;

                if (digits.length > 7) {
                    formatted = digits.slice(0, 4) + ' ' + digits.slice(4, 7) + ' ' + digits.slice(7);
                } else if (digits.length > 4) {
                    formatted = digits.slice(0, 4) + ' ' + digits.slice(4);
                }

                phone.value = formatted;
            });
        })();

        (function () {
            function setupField(field) {
                var canvas = field.querySelector('.sig-canvas');
                var hiddenInput = field.querySelector('[data-hidden-input]');
                var sigError = field.querySelector('[data-sig-error]');
                var preview = field.querySelector('[data-preview]');
                var previewImg = field.querySelector('[data-preview-img]');
                var tabButtons = field.querySelectorAll('[data-tab-btn]');
                var tabs = field.querySelectorAll('[data-tab]');
                var fileInput = field.querySelector('[data-action="upload"]');
                var clearBtn = field.querySelector('[data-action="clear"]');
                var removeBtn = field.querySelector('[data-action="remove"]');
                var pad = null;

                function setValue(value) {
                    hiddenInput.value = value || '';
                    if (value) {
                        previewImg.src = value;
                        preview.classList.remove('hidden');
                        if (sigError) sigError.classList.add('hidden');
                    } else {
                        preview.classList.add('hidden');
                    }
                }

                function showTab(name) {
                    tabs.forEach(function (t) {
                        t.classList.toggle('hidden', t.dataset.tab !== name);
                    });
                    tabButtons.forEach(function (b) {
                        var active = b.dataset.tabBtn === name;
                        b.classList.toggle('bg-white', active);
                        b.classList.toggle('shadow-sm', active);
                        b.classList.toggle('text-cyan-600', active);
                        b.classList.toggle('font-medium', active);
                        b.classList.toggle('text-slate-500', !active);
                    });
                }

                field._initPad = function () {
                    if (pad) return;
                    if (typeof SignaturePad === 'undefined') {
                        setTimeout(field._initPad, 150);
                        return;
                    }
                    var width = canvas.parentElement.offsetWidth;
                    if (!width) return;
                    canvas.width = width;
                    canvas.height = 160;
                    pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
                    pad.addEventListener('endStroke', function () {
                        setValue(pad.toDataURL());
                    });
                };

                tabButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        showTab(btn.dataset.tabBtn);
                    });
                });

                clearBtn.addEventListener('click', function () {
                    if (pad) pad.clear();
                    setValue(null);
                });

                fileInput.addEventListener('change', function (e) {
                    var file = e.target.files[0];
                    if (!file) return;
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 2MB.');
                        e.target.value = '';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (ev) {
                        setValue(ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });

                removeBtn.addEventListener('click', function () {
                    setValue(null);
                    if (pad) pad.clear();
                    fileInput.value = '';
                });

                showTab('draw');

                if (hiddenInput.value) {
                    setValue(hiddenInput.value);
                }
            }

            var fields = Array.prototype.slice.call(document.querySelectorAll('.signature-field'));
            fields.forEach(setupField);

            fields.forEach(function (field) {
                if (field.offsetParent !== null) field._initPad();
            });

            window.addEventListener('wizard:step-shown', function (e) {
                e.detail.step.querySelectorAll('.signature-field').forEach(function (field) {
                    if (field._initPad) field._initPad();
                });
            });
        })();
    </script>
</body>
</html>
