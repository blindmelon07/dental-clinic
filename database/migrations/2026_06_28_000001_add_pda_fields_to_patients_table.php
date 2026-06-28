<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Personal info additions
            $table->string('nickname')->nullable()->after('last_name');
            $table->string('religion')->nullable()->after('nickname');
            $table->string('nationality')->nullable()->after('religion');
            $table->string('occupation')->nullable()->after('nationality');
            $table->string('home_no')->nullable()->after('phone');
            $table->string('office_no')->nullable()->after('home_no');

            // Insurance & referral
            $table->string('dental_insurance')->nullable()->after('email');
            $table->date('insurance_effective_date')->nullable()->after('dental_insurance');
            $table->string('referring_person')->nullable()->after('insurance_effective_date');
            $table->text('reason_for_consultation')->nullable()->after('referring_person');

            // For minors
            $table->string('guardian_name')->nullable()->after('emergency_contact_relation');
            $table->string('guardian_occupation')->nullable()->after('guardian_name');

            // Dental history
            $table->string('previous_dentist')->nullable()->after('guardian_occupation');
            $table->date('last_dental_visit')->nullable()->after('previous_dentist');

            // Physician info
            $table->string('physician_name')->nullable()->after('last_dental_visit');
            $table->string('physician_specialty')->nullable()->after('physician_name');
            $table->text('physician_office_address')->nullable()->after('physician_specialty');
            $table->string('physician_office_number')->nullable()->after('physician_office_address');

            // Medical questionnaire (yes/no)
            $table->boolean('in_good_health')->nullable()->after('physician_office_number');
            $table->boolean('under_medical_treatment')->nullable()->after('in_good_health');
            $table->text('medical_treatment_condition')->nullable()->after('under_medical_treatment');
            $table->boolean('serious_illness_or_surgery')->nullable()->after('medical_treatment_condition');
            $table->text('serious_illness_details')->nullable()->after('serious_illness_or_surgery');
            $table->boolean('hospitalized')->nullable()->after('serious_illness_details');
            $table->text('hospitalization_details')->nullable()->after('hospitalized');
            $table->boolean('takes_prescription_meds')->nullable()->after('hospitalization_details');
            $table->boolean('uses_tobacco')->nullable()->after('takes_prescription_meds');
            $table->boolean('uses_alcohol_drugs')->nullable()->after('uses_tobacco');
            $table->json('drug_allergies')->nullable()->after('uses_alcohol_drugs');
            $table->string('drug_allergy_others')->nullable()->after('drug_allergies');
            $table->string('bleeding_time')->nullable()->after('drug_allergy_others');
            $table->boolean('is_pregnant')->nullable()->after('bleeding_time');
            $table->boolean('is_nursing')->nullable()->after('is_pregnant');
            $table->boolean('taking_birth_control')->nullable()->after('is_nursing');
            $table->string('blood_pressure')->nullable()->after('taking_birth_control');
            $table->json('medical_conditions_list')->nullable()->after('blood_pressure');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'nickname', 'religion', 'nationality', 'occupation',
                'home_no', 'office_no',
                'dental_insurance', 'insurance_effective_date', 'referring_person', 'reason_for_consultation',
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
            ]);
        });
    }
};
