<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientCertificateResource\Pages;
use App\Models\Patient;
use App\Models\PatientCertificate;
use App\Services\CertificateGenerator;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PatientCertificateResource extends Resource
{
    protected static ?string $model = PatientCertificate::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-check';
    protected static string|\UnitEnum|null   $navigationGroup = 'Medical Records';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $navigationLabel = 'Certificates & Clearances';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Document Information')
                ->schema([
                    Select::make('patient_id')
                        ->label('Patient')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $r) => $r->full_name . ' (' . $r->patient_number . ')')
                        ->searchable(['first_name', 'last_name', 'patient_number'])
                        ->preload()
                        ->required(),

                    Select::make('type')
                        ->label('Document Type')
                        ->options([
                            'certification'     => 'Certification',
                            'dental_clearance'  => 'Dental Clearance',
                            'medical_clearance' => 'Medical Clearance for Dental Treatment',
                        ])
                        ->required()
                        ->live(),

                    DatePicker::make('date_treated')
                        ->label('Date of Treatment')
                        ->required()
                        ->default(today()),

                    DatePicker::make('issue_date')
                        ->label('Date of Issue')
                        ->required()
                        ->default(today()),

                    Select::make('issued_by')
                        ->label('Issuing Dentist')
                        ->relationship('issuedBy', 'name')
                        ->default(fn () => auth()->id())
                        ->required(),
                ])->columns(2),

            Section::make('Clinical Details')
                ->schema([
                    Textarea::make('findings')
                        ->label('Findings')
                        ->rows(3)
                        ->placeholder('e.g. Impacted wisdom tooth number 48')
                        ->visible(fn (Get $get) => in_array($get('type'), ['certification', 'dental_clearance'])),

                    Textarea::make('treatment_done')
                        ->label('Treatment Done / Service Rendered')
                        ->rows(3)
                        ->placeholder('e.g. Odontectomy (surgery), Oral prophylaxis')
                        ->visible(fn (Get $get) => in_array($get('type'), ['certification', 'dental_clearance'])),

                    Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(3)
                        ->placeholder('e.g. Patient advised to rest for 2 weeks'),
                ]),

            Section::make('Medical Clearance Details')
                ->schema([
                    DatePicker::make('birthdate')
                        ->label('Patient Birthdate'),

                    Textarea::make('medical_conditions')
                        ->label('Medical Conditions')
                        ->rows(2)
                        ->placeholder('e.g. DIABETES, HYPERTENSION'),

                    CheckboxList::make('treatments')
                        ->label('Treatment to be Performed')
                        ->options([
                            'treatment_cleaning'   => 'Cleaning (simple or deep)',
                            'treatment_xray'       => 'Radiographs / X-Ray',
                            'treatment_anesthetic' => 'Local Anesthetic (with epinephrine)',
                            'treatment_extraction' => 'Extraction (multiple)',
                            'treatment_root_canal' => 'Root Canal Therapy',
                            'treatment_fillings'   => 'Fillings, Crowns, Bridges',
                        ])
                        ->columns(2),

                    TextInput::make('treatment_other')
                        ->label('Other Treatment')
                        ->placeholder('Specify other treatment'),
                ])
                ->visible(fn (Get $get) => $get('type') === 'medical_clearance')
                ->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Document Information')
                ->schema([
                    TextEntry::make('certificate_number')->label('Certificate #')->copyable(),
                    TextEntry::make('type')
                        ->badge()
                        ->formatStateUsing(fn (PatientCertificate $r) => $r->typeLabel())
                        ->color(fn (PatientCertificate $r) => match ($r->type) {
                            'certification'     => 'primary',
                            'dental_clearance'  => 'success',
                            'medical_clearance' => 'warning',
                            default             => 'gray',
                        }),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('issuedBy.name')->label('Issued By'),
                    TextEntry::make('date_treated')->label('Date Treated')->date('F d, Y'),
                    TextEntry::make('issue_date')->label('Date Issued')->date('F d, Y'),
                ])->columns(3),

            Section::make('Clinical Details')
                ->schema([
                    TextEntry::make('findings')->placeholder('—'),
                    TextEntry::make('treatment_done')->label('Treatment Done')->placeholder('—'),
                    TextEntry::make('notes')->placeholder('—'),
                ])->columns(1),

            Section::make('Generated Document')
                ->schema([
                    TextEntry::make('generated_at')
                        ->label('Last Generated')
                        ->dateTime()
                        ->placeholder('Not yet generated'),
                    TextEntry::make('file_path')
                        ->label('File')
                        ->placeholder('No file yet'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_number')
                    ->label('Certificate #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(['patient.first_name', 'patient.last_name']),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (PatientCertificate $r) => $r->typeLabel())
                    ->color(fn (PatientCertificate $r) => match ($r->type) {
                        'certification'     => 'primary',
                        'dental_clearance'  => 'success',
                        'medical_clearance' => 'warning',
                        default             => 'gray',
                    }),

                TextColumn::make('date_treated')->label('Date Treated')->date('M d, Y')->sortable(),
                TextColumn::make('issue_date')->label('Date Issued')->date('M d, Y')->sortable(),
                TextColumn::make('issuedBy.name')->label('Issued By'),

                IconColumn::make('file_path')
                    ->label('Generated')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->getStateUsing(fn (PatientCertificate $r): bool => (bool) $r->file_path),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'certification'     => 'Certification',
                        'dental_clearance'  => 'Dental Clearance',
                        'medical_clearance' => 'Medical Clearance',
                    ]),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (PatientCertificate $r) => route('certificate.print', $r))
                    ->openUrlInNewTab(),

                Action::make('generate')
                    ->label('Generate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (PatientCertificate $record) {
                        try {
                            CertificateGenerator::generate($record);
                            Notification::make()->title('Document generated successfully.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (PatientCertificate $r) => (bool) $r->file_path)
                    ->url(fn (PatientCertificate $r) => route('certificate.download', $r))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPatientCertificates::route('/'),
            'create' => Pages\CreatePatientCertificate::route('/create'),
            'view'   => Pages\ViewPatientCertificate::route('/{record}'),
            'edit'   => Pages\EditPatientCertificate::route('/{record}/edit'),
        ];
    }
}
