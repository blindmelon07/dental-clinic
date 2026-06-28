<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
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
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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

    private static function medicalConditionOptions(): array
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

    private static function drugAllergyOptions(): array
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->tabs([
                    Tab::make('Patient Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Personal Information')
                                ->schema([
                                    TextInput::make('last_name')->required()->maxLength(100),
                                    TextInput::make('first_name')->required()->maxLength(100),
                                    TextInput::make('middle_name')->maxLength(100),
                                    DatePicker::make('date_of_birth')->required()->maxDate(now())->label('Birthdate'),
                                    Select::make('gender')
                                        ->options(Gender::class)
                                        ->required()
                                        ->label('Sex'),
                                    TextInput::make('nickname')->maxLength(100),
                                    TextInput::make('religion')->maxLength(100),
                                    TextInput::make('nationality')->maxLength(100),
                                    Select::make('blood_type')
                                        ->options(['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'])
                                        ->searchable(),
                                    TextInput::make('occupation')->maxLength(150),
                                ])->columns(3),

                            Section::make('Contact & Address')
                                ->schema([
                                    Textarea::make('address')->required()->rows(2)->label('Home Address')->columnSpanFull(),
                                    TextInput::make('city')->required(),
                                    TextInput::make('home_no')->tel()->label('Home No.'),
                                    TextInput::make('office_no')->tel()->label('Office No.'),
                                    TextInput::make('phone')->required()->tel()->label('Cell / Mobile No.'),
                                    TextInput::make('email')->email()->label('Email Address'),
                                ])->columns(3),

                            Section::make('Insurance & Referral')
                                ->schema([
                                    TextInput::make('dental_insurance')->label('Dental Insurance')->maxLength(150),
                                    DatePicker::make('insurance_effective_date')->label('Effective Date'),
                                    TextInput::make('referring_person')->label('Referred By')->maxLength(150),
                                    Textarea::make('reason_for_consultation')
                                        ->label('Reason for Dental Consultation')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])->columns(3),

                            Section::make('For Minors')
                                ->schema([
                                    TextInput::make('guardian_name')->label("Parent / Guardian's Name")->maxLength(150),
                                    TextInput::make('guardian_occupation')->label("Guardian's Occupation")->maxLength(150),
                                ])->columns(2)->collapsible()->collapsed(),

                            Section::make('Emergency Contact')
                                ->schema([
                                    TextInput::make('emergency_contact_name')->label('Name'),
                                    TextInput::make('emergency_contact_phone')->tel()->label('Phone'),
                                    TextInput::make('emergency_contact_relation')->label('Relation'),
                                ])->columns(3),
                        ]),

                    Tab::make('Dental & Medical History')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Section::make('Dental History')
                                ->schema([
                                    TextInput::make('previous_dentist')->label('Previous Dentist: Dr.')->maxLength(150),
                                    DatePicker::make('last_dental_visit')->label('Last Dental Visit'),
                                ])->columns(2),

                            Section::make('Physician Information')
                                ->schema([
                                    TextInput::make('physician_name')->label('Name of Physician: Dr.')->maxLength(150),
                                    TextInput::make('physician_specialty')->label('Specialty')->maxLength(150),
                                    TextInput::make('physician_office_number')->label('Office Number')->tel(),
                                    Textarea::make('physician_office_address')->label('Office Address')->rows(2)->columnSpan(2),
                                ])->columns(3),

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
                                        ])->columns(2)->columnSpanFull(),

                                    Fieldset::make('Q3')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('serious_illness_or_surgery')->label('3. Have you ever had a serious illness or surgical operation?'),
                                            TextInput::make('serious_illness_details')->label('If yes, what illness or operation?'),
                                        ])->columns(2)->columnSpanFull(),

                                    Fieldset::make('Q4')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('hospitalized')->label('4. Have you ever been hospitalized?'),
                                            TextInput::make('hospitalization_details')->label('If yes, when and why?'),
                                        ])->columns(2)->columnSpanFull(),

                                    Fieldset::make('Q5')
                                        ->label('')
                                        ->schema([
                                            Toggle::make('takes_prescription_meds')->label('5. Are you taking any prescription / non-prescription medication?'),
                                            Textarea::make('current_medications')->label('If yes, please specify')->rows(2),
                                        ])->columns(2)->columnSpanFull(),

                                    Toggle::make('uses_tobacco')->label('6. Do you use tobacco products?'),
                                    Toggle::make('uses_alcohol_drugs')->label('7. Do you use alcohol, cocaine or other dangerous drugs?'),

                                    Fieldset::make('Q8 — Drug Allergies')
                                        ->schema([
                                            CheckboxList::make('drug_allergies')
                                                ->label('8. Are you allergic to any of the following?')
                                                ->options(self::drugAllergyOptions())
                                                ->columns(3),
                                            TextInput::make('drug_allergy_others')->label('Others (please specify)'),
                                        ])->columnSpanFull(),

                                    TextInput::make('bleeding_time')->label('9. Bleeding Time'),
                                    TextInput::make('blood_pressure')->label('12. Blood Pressure'),

                                    Fieldset::make('10. For Women Only')
                                        ->schema([
                                            Toggle::make('is_pregnant')->label('Are you pregnant?'),
                                            Toggle::make('is_nursing')->label('Are you nursing?'),
                                            Toggle::make('taking_birth_control')->label('Are you taking birth control pills?'),
                                        ])->columns(3)->columnSpanFull(),

                                    CheckboxList::make('medical_conditions_list')
                                        ->label('13. Do you have or have you had any of the following?')
                                        ->options(self::medicalConditionOptions())
                                        ->columns(3)
                                        ->columnSpanFull(),
                                ])->columns(2),

                            Section::make('Additional Notes')
                                ->schema([
                                    Textarea::make('allergies')->label('Other Allergies')->rows(3),
                                    Textarea::make('medical_conditions')->label('Other Medical Conditions')->rows(3),
                                ])->columns(2)
                                ->description('Free-text notes in addition to the questionnaire above.')
                                ->collapsible(),
                        ]),
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
                    TextEntry::make('gender')->badge(),
                    TextEntry::make('blood_type')->label('Blood Type')->placeholder('—'),
                    TextEntry::make('religion')->placeholder('—'),
                    TextEntry::make('nationality')->placeholder('—'),
                    TextEntry::make('occupation')->placeholder('—'),
                ])->columns(3),

            Section::make('Contact Details')
                ->schema([
                    TextEntry::make('phone')->label('Cell / Mobile No.'),
                    TextEntry::make('home_no')->label('Home No.')->placeholder('—'),
                    TextEntry::make('office_no')->label('Office No.')->placeholder('—'),
                    TextEntry::make('email')->placeholder('—'),
                    TextEntry::make('address')->placeholder('—'),
                    TextEntry::make('city')->placeholder('—'),
                ])->columns(3),

            Section::make('Insurance & Referral')
                ->schema([
                    TextEntry::make('dental_insurance')->label('Dental Insurance')->placeholder('—'),
                    TextEntry::make('insurance_effective_date')->label('Effective Date')->date()->placeholder('—'),
                    TextEntry::make('referring_person')->label('Referred By')->placeholder('—'),
                    TextEntry::make('reason_for_consultation')->label('Reason for Consultation')->placeholder('—')->columnSpanFull(),
                ])->columns(3),

            Section::make('Emergency Contact')
                ->schema([
                    TextEntry::make('emergency_contact_name')->label('Name')->placeholder('—'),
                    TextEntry::make('emergency_contact_phone')->label('Phone')->placeholder('—'),
                    TextEntry::make('emergency_contact_relation')->label('Relation')->placeholder('—'),
                ])->columns(3),

            Section::make('For Minors')
                ->schema([
                    TextEntry::make('guardian_name')->label("Parent / Guardian's Name")->placeholder('—'),
                    TextEntry::make('guardian_occupation')->label("Guardian's Occupation")->placeholder('—'),
                ])->columns(2),

            Section::make('Dental History')
                ->schema([
                    TextEntry::make('previous_dentist')->label('Previous Dentist')->placeholder('—'),
                    TextEntry::make('last_dental_visit')->label('Last Dental Visit')->date()->placeholder('—'),
                ])->columns(2),

            Section::make('Physician Information')
                ->schema([
                    TextEntry::make('physician_name')->label('Physician')->placeholder('—'),
                    TextEntry::make('physician_specialty')->label('Specialty')->placeholder('—'),
                    TextEntry::make('physician_office_number')->label('Office Number')->placeholder('—'),
                    TextEntry::make('physician_office_address')->label('Office Address')->placeholder('—'),
                ])->columns(2),

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
                        ->formatStateUsing(function (?array $state): string {
                            if (empty($state)) return '—';
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
                        ->formatStateUsing(function (?array $state): string {
                            if (empty($state)) return '—';
                            $map = self::medicalConditionOptions();
                            return implode(', ', array_map(fn ($k) => $map[$k] ?? $k, $state));
                        })
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])->columns(3),

            Section::make('Medical Notes')
                ->schema([
                    TextEntry::make('allergies')->label('Other Allergies')->placeholder('None recorded'),
                    TextEntry::make('medical_conditions')->label('Other Medical Conditions')->placeholder('None recorded'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_number')->searchable()->sortable()->copyable(),
                TextColumn::make('full_name')->searchable(['first_name', 'last_name'])->sortable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('gender')->badge(),
                TextColumn::make('date_of_birth')->date()->sortable(),
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
