<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\PatientResource\Pages;
use App\Models\Dentist;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Forms\Components\SignaturePad;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_patient'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('view_patient'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_patient'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('update_patient'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->can('delete_patient'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_patient'); }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'middle_name', 'last_name', 'patient_number', 'phone'];
    }

    public static function religionOptions(): array
    {
        return [
            'Roman Catholic'  => 'Roman Catholic',
            'Christian'       => 'Christian',
            'Protestant'      => 'Protestant',
            'Baptist'         => 'Baptist',
            'Iglesia ni Cristo' => 'Iglesia ni Cristo',
            'Born Again Christian' => 'Born Again Christian',
            'Seventh-day Adventist' => 'Seventh-day Adventist',
            'Jehovah\'s Witness' => 'Jehovah\'s Witness',
            'Mormon (LDS)'    => 'Mormon (LDS)',
            'Orthodox Christian' => 'Orthodox Christian',
            'Islam'           => 'Islam',
            'Buddhism'        => 'Buddhism',
            'Hinduism'        => 'Hinduism',
            'Judaism'         => 'Judaism',
            'Sikhism'         => 'Sikhism',
            'Taoism'          => 'Taoism',
            'Atheist'         => 'Atheist',
            'Agnostic'        => 'Agnostic',
            'None'            => 'None',
            'Other'           => 'Other',
        ];
    }

    public static function bloodTypeOptions(): array
    {
        return ['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'];
    }

    public static function nationalityOptions(): array
    {
        $nationalities = [
            'Afghan', 'Albanian', 'Algerian', 'American', 'Andorran', 'Angolan', 'Antiguan', 'Argentine',
            'Armenian', 'Australian', 'Austrian', 'Azerbaijani', 'Bahamian', 'Bahraini', 'Bangladeshi',
            'Barbadian', 'Belarusian', 'Belgian', 'Belizean', 'Beninese', 'Bhutanese', 'Bolivian',
            'Bosnian', 'Motswana', 'Brazilian', 'British', 'Bruneian', 'Bulgarian', 'Burkinabe', 'Burmese',
            'Burundian', 'Cambodian', 'Cameroonian', 'Canadian', 'Cape Verdean', 'Central African', 'Chadian',
            'Chilean', 'Chinese', 'Colombian', 'Comoran', 'Congolese', 'Costa Rican', 'Croatian', 'Cuban',
            'Cypriot', 'Czech', 'Danish', 'Djiboutian', 'Dominican', 'Dutch', 'East Timorese', 'Ecuadorian',
            'Egyptian', 'Emirati', 'English', 'Equatorial Guinean', 'Eritrean', 'Estonian', 'Eswatini',
            'Ethiopian', 'Fijian', 'Filipino', 'Finnish', 'French', 'Gabonese', 'Gambian', 'Georgian',
            'German', 'Ghanaian', 'Greek', 'Grenadian', 'Guatemalan', 'Guinean', 'Guyanese', 'Haitian',
            'Honduran', 'Hungarian', 'Icelandic', 'Indian', 'Indonesian', 'Iranian', 'Iraqi', 'Irish',
            'Israeli', 'Italian', 'Ivorian', 'Jamaican', 'Japanese', 'Jordanian', 'Kazakhstani', 'Kenyan',
            'Kittitian', 'Kiribati', 'Korean (North)', 'Korean (South)', 'Kosovar', 'Kuwaiti', 'Kyrgyz',
            'Lao', 'Latvian', 'Lebanese', 'Liberian', 'Libyan', 'Liechtensteiner', 'Lithuanian',
            'Luxembourgish', 'Macedonian', 'Malagasy', 'Malawian', 'Malaysian', 'Maldivian', 'Malian',
            'Maltese', 'Marshallese', 'Mauritanian', 'Mauritian', 'Mexican', 'Micronesian', 'Moldovan',
            'Monacan', 'Mongolian', 'Montenegrin', 'Moroccan', 'Mozambican', 'Namibian', 'Nauruan',
            'Nepalese', 'New Zealander', 'Nicaraguan', 'Nigerian', 'Nigerien', 'Norwegian', 'Omani',
            'Pakistani', 'Palauan', 'Palestinian', 'Panamanian', 'Papua New Guinean', 'Paraguayan',
            'Peruvian', 'Polish', 'Portuguese', 'Qatari', 'Romanian', 'Russian', 'Rwandan', 'Salvadoran',
            'Samoan', 'San Marinese', 'Sao Tomean', 'Saudi Arabian', 'Scottish', 'Senegalese', 'Serbian',
            'Seychellois', 'Sierra Leonean', 'Singaporean', 'Slovak', 'Slovenian', 'Solomon Islander',
            'Somali', 'South African', 'South Sudanese', 'Spanish', 'Sri Lankan', 'Sudanese', 'Surinamese',
            'Swedish', 'Swiss', 'Syrian', 'Taiwanese', 'Tajik', 'Tanzanian', 'Thai', 'Togolese', 'Tongan',
            'Trinidadian', 'Tunisian', 'Turkish', 'Turkmen', 'Tuvaluan', 'Ugandan', 'Ukrainian', 'Uruguayan',
            'Uzbekistani', 'Vanuatuan', 'Vatican', 'Venezuelan', 'Vietnamese', 'Welsh', 'Yemeni', 'Zambian',
            'Zimbabwean', 'Other',
        ];

        sort($nationalities);

        // Pin Filipino at the top for a Philippine clinic, followed by the rest alphabetically.
        $nationalities = array_values(array_unique(array_merge(['Filipino'], $nationalities)));

        return array_combine($nationalities, $nationalities);
    }

    public static function cityOptions(): array
    {
        $cities = [
            'Alaminos', 'Angeles', 'Antipolo', 'Bacolod', 'Bacoor', 'Bago', 'Baguio', 'Bais', 'Balanga',
            'Batac', 'Batangas City', 'Bayawan', 'Baybay', 'Bayugan', 'Biñan', 'Bislig', 'Bogo', 'Borongan',
            'Butuan', 'Cabadbaran', 'Cabanatuan', 'Cabuyao', 'Cadiz', 'Cagayan de Oro', 'Calamba', 'Calapan',
            'Calbayog', 'Caloocan', 'Candon', 'Canlaon', 'Carcar', 'Carmona', 'Catbalogan', 'Cauayan',
            'Cavite City', 'Cebu City', 'Cotabato City', 'Dagupan', 'Danao', 'Dapitan', 'Dasmariñas',
            'Davao City', 'Digos', 'Dipolog', 'Dumaguete', 'El Salvador', 'Escalante', 'Gapan',
            'General Santos', 'General Trias', 'Gingoog', 'Guihulngan', 'Himamaylan', 'Ilagan', 'Iligan',
            'Iloilo City', 'Imus', 'Iriga', 'Isabela City', 'Kabankalan', 'Kidapawan', 'Koronadal',
            'La Carlota', 'Lamitan', 'Laoag', 'Lapu-Lapu', 'Las Piñas', 'Legazpi', 'Ligao', 'Lipa',
            'Lucena', 'Maasin', 'Mabalacat', 'Makati', 'Malabon', 'Malaybalay', 'Malolos', 'Mandaluyong',
            'Mandaue', 'Manila', 'Marawi', 'Marikina', 'Masbate City', 'Mati', 'Meycauayan', 'Muntinlupa',
            'Muñoz', 'Naga (Camarines Sur)', 'Naga (Cebu)', 'Navotas', 'Olongapo', 'Ormoc', 'Oroquieta',
            'Ozamiz', 'Pagadian', 'Palayan', 'Panabo', 'Parañaque', 'Pasay', 'Pasig', 'Passi', 'Pateros',
            'Puerto Princesa', 'Quezon City', 'Sagay', 'Samal', 'San Carlos (Negros Occidental)',
            'San Carlos (Pangasinan)', 'San Fernando (La Union)', 'San Fernando (Pampanga)',
            'San Jose (Nueva Ecija)', 'San Jose del Monte', 'San Juan', 'San Pablo', 'San Pedro',
            'Santa Rosa', 'Santiago', 'Silay', 'Sipalay', 'Sorsogon City', 'Surigao', 'Tabaco', 'Tabuk',
            'Tacloban', 'Tacurong', 'Tagaytay', 'Tagbilaran', 'Taguig', 'Tagum', 'Talisay (Cebu)',
            'Talisay (Negros Occidental)', 'Tanauan', 'Tandag', 'Tangub', 'Tanjay', 'Tarlac City',
            'Tayabas', 'Toledo', 'Trece Martires', 'Tuguegarao', 'Urdaneta', 'Valencia', 'Valenzuela',
            'Victorias', 'Vigan', 'Zamboanga City', 'Other',
        ];

        return array_combine($cities, $cities);
    }

    public static function provinceOptions(): array
    {
        $provinces = [
            'Metro Manila (NCR)', 'Abra', 'Agusan del Norte', 'Agusan del Sur', 'Aklan', 'Albay',
            'Antique', 'Apayao', 'Aurora', 'Basilan', 'Bataan', 'Batanes', 'Batangas', 'Benguet',
            'Biliran', 'Bohol', 'Bukidnon', 'Bulacan', 'Cagayan', 'Camarines Norte', 'Camarines Sur',
            'Camiguin', 'Capiz', 'Catanduanes', 'Cavite', 'Cebu', 'Cotabato', 'Davao de Oro',
            'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental', 'Dinagat Islands',
            'Eastern Samar', 'Guimaras', 'Ifugao', 'Ilocos Norte', 'Ilocos Sur', 'Iloilo', 'Isabela',
            'Kalinga', 'La Union', 'Laguna', 'Lanao del Norte', 'Lanao del Sur', 'Leyte',
            'Maguindanao del Norte', 'Maguindanao del Sur', 'Marinduque', 'Masbate', 'Misamis Occidental',
            'Misamis Oriental', 'Mountain Province', 'Negros Occidental', 'Negros Oriental',
            'Northern Samar', 'Nueva Ecija', 'Nueva Vizcaya', 'Occidental Mindoro', 'Oriental Mindoro',
            'Palawan', 'Pampanga', 'Pangasinan', 'Quezon', 'Quirino', 'Rizal', 'Romblon',
            'Samar (Western Samar)', 'Sarangani', 'Siquijor', 'Sorsogon', 'South Cotabato',
            'Southern Leyte', 'Sultan Kudarat', 'Sulu', 'Surigao del Norte', 'Surigao del Sur',
            'Tarlac', 'Tawi-Tawi', 'Zambales', 'Zamboanga del Norte', 'Zamboanga del Sur',
            'Zamboanga Sibugay', 'Other',
        ];

        return array_combine($provinces, $provinces);
    }

    public static function medicalConditionOptions(): array
    {
        return [
            'high_blood_pressure'        => 'High Blood Pressure',
            'low_blood_pressure'         => 'Low Blood Pressure',
            'epilepsy_convulsions'       => 'Epilepsy / Convulsions',
            'aids_hiv'                   => 'AIDS or HIV Infection',
            'sexually_transmitted'       => 'Sexually Transmitted Disease',
            'stomach_ulcers'             => 'Stomach Troubles / Ulcers',
            'fainting_seizure'           => 'Fainting Seizure',
            'rapid_weight_loss'          => 'Rapid Weight Loss',
            'radiation_therapy'          => 'Radiation Therapy',
            'joint_replacement'          => 'Joint Replacement / Implant',
            'heart_surgery'              => 'Heart Surgery',
            'heart_attack'               => 'Heart Attack',
            'thyroid_problem'            => 'Thyroid Problem',
            'heart_disease'              => 'Heart Disease',
            'heart_murmur'               => 'Heart Murmur',
            'hepatitis_liver_disease'    => 'Hepatitis / Liver Disease',
            'rheumatic_fever'            => 'Rheumatic Fever',
            'hay_fever_allergies'        => 'Hay Fever / Allergies',
            'respiratory_problems'       => 'Respiratory Problems',
            'hepatitis_jaundice'         => 'Hepatitis / Jaundice',
            'tuberculosis'               => 'Tuberculosis',
            'swollen_ankles'             => 'Swollen Ankles',
            'kidney_disease'             => 'Kidney Disease',
            'diabetes'                   => 'Diabetes',
            'chest_pain'                 => 'Chest Pain',
            'stroke'                     => 'Stroke',
            'cancer_tumors'              => 'Cancer / Tumors',
            'anemia'                     => 'Anemia',
            'angina'                     => 'Angina',
            'asthma'                     => 'Asthma',
            'emphysema'                  => 'Emphysema',
            'bleeding_problems'          => 'Bleeding Problems',
            'blood_diseases'             => 'Blood Diseases',
            'head_injuries'              => 'Head Injuries',
            'arthritis_rheumatism'       => 'Arthritis / Rheumatism',
        ];
    }

    public static function drugAllergyOptions(): array
    {
        return [
            'local_anesthetic' => 'Local Anesthetic (e.g. Lidocaine)',
            'penicillin'       => 'Penicillin',
            'antibiotics'      => 'Antibiotics',
            'sulfa_drugs'      => 'Sulfa Drugs',
            'aspirin'          => 'Aspirin',
            'latex'            => 'Latex',
        ];
    }

    public static function patientInformationSchema(): array
    {
        return [
                            Section::make('Personal Information')
                                ->schema([
                                    TextInput::make('last_name')->required()->maxLength(100),
                                    TextInput::make('first_name')->required()->maxLength(100),
                                    TextInput::make('middle_name')->maxLength(100),
                                    DatePicker::make('date_of_birth')
                                        ->required()
                                        ->maxDate(now())
                                        ->label('Birthdate')
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $set('age_display', $state
                                                ? \Carbon\Carbon::parse($state)->age . ' years old'
                                                : null);
                                        }),
                                    TextInput::make('age_display')
                                        ->label('Age')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('—'),
                                    Select::make('gender')
                                        ->options(Gender::class)
                                        ->required()
                                        ->label('Sex'),
                                    TextInput::make('nickname')->maxLength(100),
                                    Select::make('religion')
                                        ->options(self::religionOptions())
                                        ->searchable()
                                        ->native(false),
                                    Select::make('nationality')
                                        ->options(self::nationalityOptions())
                                        ->searchable()
                                        ->native(false),
                                    Select::make('blood_type')
                                        ->options(self::bloodTypeOptions())
                                        ->searchable(),
                                    TextInput::make('occupation')->maxLength(150),
                                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

                            Section::make('Contact & Address')
                                ->schema([
                                    Textarea::make('address')->required()->rows(2)->label('Home Address')->columnSpanFull(),
                                    Select::make('city')
                                        ->options(self::cityOptions())
                                        ->searchable()
                                        ->native(false),
                                    Select::make('province')
                                        ->options(self::provinceOptions())
                                        ->searchable()
                                        ->native(false),
                                    TextInput::make('home_no')->tel()->label('Home No.'),
                                    TextInput::make('office_no')->tel()->label('Office No.'),
                                    TextInput::make('phone')
                                        ->required()
                                        ->tel()
                                        ->label('Cell / Mobile No.')
                                        ->placeholder('0917 123 4567')
                                        ->mask('9999 999 9999')
                                        ->stripCharacters(' ')
                                        ->rule('regex:/^[0-9+\s]{7,20}$/'),
                                    TextInput::make('email')->email()->label('Email Address'),
                                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

                            Section::make('Insurance & Referral')
                                ->schema([
                                    TextInput::make('dental_insurance')->label('Dental Insurance')->maxLength(150),
                                    DatePicker::make('insurance_effective_date')->label('Effective Date'),
                                    TextInput::make('referring_person')->label('Referred By')->maxLength(150),
                                    Textarea::make('reason_for_consultation')
                                        ->label('Reason for Dental Consultation')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

                            Section::make('For Minors')
                                ->schema([
                                    TextInput::make('guardian_name')->label("Parent / Guardian's Name")->maxLength(150),
                                    TextInput::make('guardian_occupation')->label("Guardian's Occupation")->maxLength(150),
                                ])->columns(['default' => 1, 'sm' => 2])->collapsible()->collapsed(),

                            Section::make('Emergency Contact')
                                ->schema([
                                    TextInput::make('emergency_contact_name')->label('Name'),
                                    TextInput::make('emergency_contact_phone')->tel()->label('Phone'),
                                    TextInput::make('emergency_contact_relation')->label('Relation'),
                                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),
        ];
    }

    public static function dentalMedicalHistorySchema(): array
    {
        return [
                            Section::make('Dental History')
                                ->schema([
                                    TextInput::make('previous_dentist')->label('Previous Dentist: Dr.')->maxLength(150),
                                    DatePicker::make('last_dental_visit')->label('Last Dental Visit'),
                                ])->columns(['default' => 1, 'sm' => 2]),

                            Section::make('Physician Information')
                                ->schema([
                                    TextInput::make('physician_name')->label('Name of Physician: Dr.')->maxLength(150),
                                    TextInput::make('physician_specialty')->label('Specialty')->maxLength(150),
                                    TextInput::make('physician_office_number')->label('Office Number')->tel(),
                                    Textarea::make('physician_office_address')->label('Office Address')->rows(2)->columnSpanFull(),
                                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

                            Section::make('Medical Questionnaire')
                                ->schema([
                                    Fieldset::make('Q1–Q2')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('in_good_health')->label('1. Are you in good health?'),
                                            Toggle::make('under_medical_treatment')->label('2. Are you under medical treatment now?'),
                                            TextInput::make('medical_treatment_condition')
                                                ->label('If yes, what is the condition being treated?')
                                                ->columnSpanFull(),
                                        ])->columns(['default' => 1, 'sm' => 2])->columnSpanFull(),

                                    Fieldset::make('Q3')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('serious_illness_or_surgery')->label('3. Have you ever had a serious illness or surgical operation?'),
                                            TextInput::make('serious_illness_details')->label('If yes, what illness or operation?'),
                                        ])->columns(['default' => 1, 'sm' => 2])->columnSpanFull(),

                                    Fieldset::make('Q4')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('hospitalized')->label('4. Have you ever been hospitalized?'),
                                            TextInput::make('hospitalization_details')->label('If yes, when and why?'),
                                        ])->columns(['default' => 1, 'sm' => 2])->columnSpanFull(),

                                    Fieldset::make('Q5')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('takes_prescription_meds')->label('5. Are you taking any prescription / non-prescription medication?'),
                                            Textarea::make('current_medications')->label('If yes, please specify')->rows(2),
                                        ])->columns(['default' => 1, 'sm' => 2])->columnSpanFull(),

                                    Toggle::make('uses_tobacco')->label('6. Do you use tobacco products?'),
                                    Toggle::make('uses_alcohol_drugs')->label('7. Do you use alcohol, cocaine or other dangerous drugs?'),

                                    Fieldset::make('Q8 — Drug Allergies')
                                        ->schema([
                                            CheckboxList::make('drug_allergies')
                                                ->label('8. Are you allergic to any of the following?')
                                                ->options(self::drugAllergyOptions())
                                                ->columns(['default' => 1, 'sm' => 2]),
                                            TextInput::make('drug_allergy_others')->label('Others (please specify)'),
                                        ])->columnSpanFull(),

                                    TextInput::make('bleeding_time')->label('9. Bleeding Time'),
                                    TextInput::make('blood_pressure')->label('12. Blood Pressure'),

                                    Fieldset::make('10. For Women Only')
                                        ->schema([
                                            Toggle::make('is_pregnant')->label('Are you pregnant?'),
                                            Toggle::make('is_nursing')->label('Are you nursing?'),
                                            Toggle::make('taking_birth_control')->label('Are you taking birth control pills?'),
                                        ])->columns(['default' => 1, 'sm' => 3])->columnSpanFull(),

                                    CheckboxList::make('medical_conditions_list')
                                        ->label('13. Do you have or have you had any of the following?')
                                        ->options(self::medicalConditionOptions())
                                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                                        ->columnSpanFull(),
                                ])->columns(['default' => 1, 'sm' => 2]),

                            Section::make('Additional Notes')
                                ->schema([
                                    Textarea::make('allergies')->label('Other Allergies')->rows(3),
                                    Textarea::make('medical_conditions')->label('Other Medical Conditions')->rows(3),
                                ])->columns(['default' => 1, 'sm' => 2])
                                ->description('Free-text notes in addition to the questionnaire above.')
                                ->collapsible(),
        ];
    }

    public static function informedConsentSchema(): array
    {
        return [
                            Section::make('TREATMENT TO BE DONE')
                                ->description('I understand and consent to have any treatment done by the dentist after the procedure, the risks & benefits & cost have been fully explained. These treatments include, but are not limited to: x rays, cleanings, periodontal treatments, fillings, crowns, bridges, all types of extraction, root canals, &/or dentures, local anesthetics & surgical cases.')
                                ->schema([
                                    TextInput::make('consent_treatment_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('DRUGS & MEDICATIONS')
                                ->description('I understand that antibiotics, analgesics & other medications can cause allergic reactions like redness & swelling of tissues, pain, itching, vomiting, &/or anaphylactic shock.')
                                ->schema([
                                    TextInput::make('consent_drugs_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('CHANGES IN TREATMENT PLAN')
                                ->description('I understand that during treatment it may be necessary to change/add procedures because of conditions found while working on the teeth that was not discovered during examination. For example, root canal therapy may be needed following routine restorative procedures. I give my permission to the dentist to make any/all changes and additions as necessary w/ my responsibility to pay all the costs agreed.')
                                ->schema([
                                    TextInput::make('consent_treatment_plan_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('RADIOGRAPH')
                                ->description('I understand that an x-ray shot or a radiograph maybe necessary as part of diagnostic aid to come up with tentative diagnosis at my dental problem and to make a good treatment plan, but this will not give me a 100% assurance for the accuracy of the treatment since all dental treatments are subject to unpredictable complications that later on may lead to sudden change of treatment plan and subject to new charges.')
                                ->schema([
                                    TextInput::make('consent_radiograph_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('REMOVAL OF TEETH')
                                ->description('I understand that alternatives to tooth removal (root canal therapy, crowns & periodontal surgery, etc.) & I completely understand these alternatives, including their risk & benefits prior to authorizing the dentist to remove teeth & any other structures necessary for reasons above. I understand that removing teeth does not always remove all the infections, if present, & it may be necessary to have further treatment. I understand the risk involved in having teeth removed, such as: pain, swelling, spread of infection, dry socket, fractured jaw, loss of feeling on the teeth, lips, tongue & surrounding tissue that can last for an indefinite period of time. I understand that I may need further treatment under a specialist if complications arise during or following treatment.')
                                ->schema([
                                    TextInput::make('consent_removal_of_teeth_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('CROWNS (CAPS) & BRIDGES')
                                ->description('Preparing a tooth may irritate the nerve tissue in the center of the tooth, leaving the tooth extra sensitive to heat, cold & pressure. Treating such irritation may involve using special toothpastes, mouth rinses or root canal therapy. I understand that sometimes it is not possible to match the color of natural teeth exactly with artificial teeth. I further understand that I may be wearing temporary crowns, which may come off easily & that I must be careful to ensure that they are kept on until the permanent crowns are delivered. It is my responsibility to return for permanent cementation within 20 days from tooth preparation, as excessive days delay may allow for tooth movement, which may necessitate a remake of the crown, bridge/cap. I understand there will be additional charges for remakes due to my delaying of permanent cementation, & I realize that final opportunity to make changes in my new crown, bridges or cap (including shape, fit, size & color) will be before permanent cementation.')
                                ->schema([
                                    TextInput::make('consent_crowns_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('ENDODONTICS (ROOT CANAL)')
                                ->description('I understand there is no guarantee that a root canal treatment will save a tooth & that complications can occur from the treatment & that occasionally root canal filling materials may extend through the tooth which does not necessarily affect the success of the treatment. I understand that endodontic files & drills are very fine instruments & stresses vented in their manufacture & calcifications present in teeth can cause them to break during use. I understand that referral to the endodontist for additional treatments may be necessary following any root canal treatment & I agree that I am responsible for any additional cost for treatment performed by the endodontist. I understand that a tooth may require removal in spite of all efforts to save it.')
                                ->schema([
                                    TextInput::make('consent_endodontics_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('PERIODONTAL DISEASE')
                                ->description('I understand that periodontal disease is a serious condition causing gum & bone inflammation &/or loss & that can lead eventually to the loss of my teeth. I understand the alternative treatment plans to correct periodontal disease, including gum surgery tooth extractions with or without replacement. I understand that undertaking any dental procedures may have future adverse effect on my periodontal conditions.')
                                ->schema([
                                    TextInput::make('consent_periodontal_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('FILLINGS')
                                ->description('I understand that care must be exercised in chewing on fillings, especially during the first 24 hours to avoid breakage. I understand that a more extensive filling or a crown may be required, as additional decay or fracture may become evident after initial excavation. I understand that significant sensitivity is a common, but usually temporary, after-effect of a new placement of filling. I further understand that filling a tooth may irritate the nerve tissue creating sensitivity & treating such sensitivity could require root canal therapy or extractions.')
                                ->schema([
                                    TextInput::make('consent_fillings_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('DENTURES')
                                ->description('I understand that wearing of dentures can be difficult. Sore spots, altered speech & difficulty in eating are common problems. Immediate dentures (placement of denture immediately after extractions) may be painful. Immediate dentures may require considerable adjusting & several relines. I understand that it is my responsibility to return for delivery of dentures. I understand that failure to keep my delivery appointment may result in poorly fitted dentures. If a remake is required due to my delays of more than 30 days, there will be additional charges. A permanent reline will be needed later, which is not included in the initial fee. I understand that all adjustment or alterations of any kind after this initial period is subject to charges.')
                                ->schema([
                                    TextInput::make('consent_dentures_initials')->label('Initial')->maxLength(10),
                                ])->columns(1),

                            Section::make('Authorization & Signature')
                                ->description('I understand that dentistry is not an exact science and that no dentist can properly guarantee accurate results all the time. I hereby authorize any of the doctors/dental auxiliaries to proceed with & perform the dental restorations & treatments as explained to me. I understand that these are subject to modification depending on undiagnosable circumstances that may arise during the course of treatment. I understand that regarding any dental insurance coverage I may have, I am responsible for payment of dental fees. I agree to pay any attorney\'s fees, collection fee, or court costs that may be incurred to satisfy any obligation to this office. All treatment were properly explained to me & any untoward circumstances that may arise during the procedure, the attending dentist will not be held liable since it is my free will, with full trust & confidence in him/her, to undergo dental treatment under his/her care.')
                                ->schema([
                                    Toggle::make('consent_agreed')
                                        ->label('Patient / Parent / Guardian agrees to the above informed consent')
                                        ->required()
                                        ->accepted()
                                        ->columnSpanFull(),
                                    DatePicker::make('consent_date')
                                        ->label('Date Signed')
                                        ->maxDate(now()),
                                    Select::make('consent_dentist_id')
                                        ->label('Dentist Name')
                                        ->relationship('dentist', 'id')
                                        ->getOptionLabelFromRecordUsing(fn (Dentist $record) => $record->user->name)
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            $dentist = $state ? Dentist::find($state) : null;
                                            $set('consent_dentist_name', $dentist?->user->name);
                                            $set('consent_dentist_signature', $dentist?->signature);
                                        }),
                                    Hidden::make('consent_dentist_name')->dehydrated(),
                                    SignaturePad::make('consent_patient_signature')
                                        ->label('Patient / Parent / Guardian Signature'),
                                    SignaturePad::make('consent_dentist_signature')
                                        ->label('Dentist E-Signature')
                                        ->disabled()
                                        ->dehydrated(),
                                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->tabs([
                    Tab::make('Patient Information')
                        ->icon('heroicon-o-user')
                        ->schema(self::patientInformationSchema()),
                    Tab::make('Dental & Medical History')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema(self::dentalMedicalHistorySchema()),
                    Tab::make('Informed Consent')
                        ->icon('heroicon-o-document-check')
                        ->schema(self::informedConsentSchema()),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Information')
                ->schema([
                    TextEntry::make('patient_number')->label('Patient #')->copyable(),
                    TextEntry::make('full_name')->label('Full Name'),
                    TextEntry::make('nickname')->placeholder('—'),
                    TextEntry::make('date_of_birth')->date()->label('Date of Birth'),
                    TextEntry::make('age')->label('Age')->suffix(' years old'),
                    TextEntry::make('gender')->badge(),
                    TextEntry::make('blood_type')->label('Blood Type')->placeholder('—'),
                    TextEntry::make('religion')->placeholder('—'),
                    TextEntry::make('nationality')->placeholder('—'),
                    TextEntry::make('occupation')->placeholder('—'),
                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

            Section::make('Contact Details')
                ->schema([
                    TextEntry::make('phone')->label('Cell / Mobile No.'),
                    TextEntry::make('home_no')->label('Home No.')->placeholder('—'),
                    TextEntry::make('office_no')->label('Office No.')->placeholder('—'),
                    TextEntry::make('email')->placeholder('—'),
                    TextEntry::make('address')->placeholder('—'),
                    TextEntry::make('city')->placeholder('—'),
                    TextEntry::make('province')->placeholder('—'),
                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

            Section::make('Insurance & Referral')
                ->schema([
                    TextEntry::make('dental_insurance')->label('Dental Insurance')->placeholder('—'),
                    TextEntry::make('insurance_effective_date')->label('Effective Date')->date()->placeholder('—'),
                    TextEntry::make('referring_person')->label('Referred By')->placeholder('—'),
                    TextEntry::make('reason_for_consultation')->label('Reason for Consultation')->placeholder('—')->columnSpanFull(),
                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

            Section::make('Emergency Contact')
                ->schema([
                    TextEntry::make('emergency_contact_name')->label('Name')->placeholder('—'),
                    TextEntry::make('emergency_contact_phone')->label('Phone')->placeholder('—'),
                    TextEntry::make('emergency_contact_relation')->label('Relation')->placeholder('—'),
                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

            Section::make('For Minors')
                ->schema([
                    TextEntry::make('guardian_name')->label("Parent / Guardian's Name")->placeholder('—'),
                    TextEntry::make('guardian_occupation')->label("Guardian's Occupation")->placeholder('—'),
                ])->columns(['default' => 1, 'sm' => 2]),

            Section::make('Dental History')
                ->schema([
                    TextEntry::make('previous_dentist')->label('Previous Dentist')->placeholder('—'),
                    TextEntry::make('last_dental_visit')->label('Last Dental Visit')->date()->placeholder('—'),
                ])->columns(['default' => 1, 'sm' => 2]),

            Section::make('Physician Information')
                ->schema([
                    TextEntry::make('physician_name')->label('Physician')->placeholder('—'),
                    TextEntry::make('physician_specialty')->label('Specialty')->placeholder('—'),
                    TextEntry::make('physician_office_number')->label('Office Number')->placeholder('—'),
                    TextEntry::make('physician_office_address')->label('Office Address')->placeholder('—'),
                ])->columns(['default' => 1, 'sm' => 2]),

            Section::make('Medical Questionnaire')
                ->schema([
                    IconEntry::make('in_good_health')->label('In good health?')->boolean(),
                    IconEntry::make('under_medical_treatment')->label('Under medical treatment?')->boolean(),
                    TextEntry::make('medical_treatment_condition')->label('Condition being treated')->placeholder('—'),
                    IconEntry::make('serious_illness_or_surgery')->label('Serious illness or surgery?')->boolean(),
                    TextEntry::make('serious_illness_details')->label('Illness / operation details')->placeholder('—'),
                    IconEntry::make('hospitalized')->label('Ever hospitalized?')->boolean(),
                    TextEntry::make('hospitalization_details')->label('Hospitalization details')->placeholder('—'),
                    IconEntry::make('takes_prescription_meds')->label('Takes prescription meds?')->boolean(),
                    TextEntry::make('current_medications')->label('Medications')->placeholder('—'),
                    IconEntry::make('uses_tobacco')->label('Uses tobacco?')->boolean(),
                    IconEntry::make('uses_alcohol_drugs')->label('Uses alcohol / drugs?')->boolean(),
                    TextEntry::make('drug_allergies')
                        ->label('Drug Allergies')
                        ->formatStateUsing(function ($state): string {
                            if (is_string($state)) $state = json_decode($state, true);
                            if (empty($state) || !is_array($state)) return '—';
                            $map = self::drugAllergyOptions();
                            return implode(', ', array_map(fn ($k) => $map[$k] ?? $k, $state));
                        })
                        ->placeholder('—'),
                    TextEntry::make('drug_allergy_others')->label('Other drug allergy')->placeholder('—'),
                    TextEntry::make('bleeding_time')->label('Bleeding Time')->placeholder('—'),
                    TextEntry::make('blood_pressure')->label('Blood Pressure')->placeholder('—'),
                    IconEntry::make('is_pregnant')->label('Pregnant?')->boolean(),
                    IconEntry::make('is_nursing')->label('Nursing?')->boolean(),
                    IconEntry::make('taking_birth_control')->label('Birth control pills?')->boolean(),
                    TextEntry::make('medical_conditions_list')
                        ->label('Medical Conditions')
                        ->formatStateUsing(function ($state): string {
                            if (is_string($state)) $state = json_decode($state, true);
                            if (empty($state) || !is_array($state)) return '—';
                            $map = self::medicalConditionOptions();
                            return implode(', ', array_map(fn ($k) => $map[$k] ?? $k, $state));
                        })
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])->columns(['default' => 1, 'md' => 2, 'lg' => 3]),

            Section::make('Medical Notes')
                ->schema([
                    TextEntry::make('allergies')->label('Other Allergies')->placeholder('None recorded'),
                    TextEntry::make('medical_conditions')->label('Other Medical Conditions')->placeholder('None recorded'),
                ])->columns(['default' => 1, 'sm' => 2]),

            Section::make('Informed Consent')
                ->schema([
                    \Filament\Infolists\Components\IconEntry::make('consent_agreed')
                        ->label('Patient Agreed')
                        ->boolean(),
                    TextEntry::make('consent_date')->label('Date Signed')->date()->placeholder('—'),
                    TextEntry::make('consent_dentist_name')->label('Dentist Name')->placeholder('—'),
                    TextEntry::make('consent_patient_signature')
                        ->label('Patient / Parent / Guardian Signature')
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? '<img src="' . e($state) . '" class="max-h-32 border border-gray-300 rounded-lg bg-white p-1" />'
                            : '—'),
                    TextEntry::make('consent_dentist_signature')
                        ->label('Dentist E-Signature')
                        ->html()
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? '<img src="' . e($state) . '" class="max-h-32 border border-gray-300 rounded-lg bg-white p-1" />'
                            : '—'),
                ])->columns(3)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_number')->searchable()->sortable()->copyable(),
                TextColumn::make('full_name')->searchable(['first_name', 'last_name'])->sortable(),
                TextColumn::make('formatted_phone')->label('Phone')->searchable(query: fn (Builder $query, string $search): Builder => $query->where('phone', 'like', "%{$search}%")),
                TextColumn::make('gender')->badge(),
                TextColumn::make('date_of_birth')->date()->sortable(),
                TextColumn::make('age')
                    ->label('Age')
                    ->suffix(' yrs')
                    ->sortable(false),
                TextColumn::make('appointments_count')->counts('appointments')->sortable()->label('Visits'),
                TextColumn::make('next_cleaning_due')
                    ->label('Cleaning Due')
                    ->date('M d, Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (?Patient $record): string => match (true) {
                        $record?->next_cleaning_due === null            => 'gray',
                        $record->next_cleaning_due->isPast()           => 'danger',
                        $record->next_cleaning_due->diffInDays() <= 30 => 'warning',
                        default                                         => 'success',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? \Carbon\Carbon::parse($state)->format('M d, Y') : '—')
                    ->toggleable(),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')->options(Gender::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PatientResource\RelationManagers\AppointmentsRelationManager::class,
            PatientResource\RelationManagers\DentalRecordsRelationManager::class,
            PatientResource\RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view'   => Pages\ViewPatient::route('/{record}'),
            'edit'   => Pages\EditPatient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('appointments');
    }
}
