<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DentalRecordResource\Pages;
use App\Models\DentalRecord;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Models\XrayType;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DentalRecordResource extends Resource
{
    protected static ?string $model = DentalRecord::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool   { return auth()->user()?->can('view_any_dental_record'); }
    public static function canView(Model $record): bool { return auth()->user()?->can('view_dental_record'); }
    public static function canCreate(): bool    { return auth()->user()?->can('create_dental_record'); }
    public static function canEdit(Model $record): bool { return auth()->user()?->can('update_dental_record'); }
    public static function canDelete(Model $record): bool { return auth()->user()?->can('delete_dental_record'); }
    public static function canDeleteAny(): bool { return auth()->user()?->can('delete_dental_record'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                // "Create & create another" resets the form data but Filament's tabs
                // keep whatever tab was active client-side (Alpine state survives the
                // Livewire re-render). Jump back to the first tab when that happens.
                ->extraAlpineAttributes([
                    'x-on:dental-record-tabs-reset.window' => 'tab = JSON.parse($refs.tabsData.value)[0]',
                ])
                ->tabs([
                    Tab::make('Visit & Assessment')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            static::visitInformationSection(),
                            static::clinicalAssessmentSection(),
                        ]),
                    Tab::make('Tooth Chart')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            ViewField::make('tooth_chart')
                                ->label('')
                                ->view('filament.forms.components.tooth-chart'),
                        ]),
                    Tab::make('Prescription & X-Rays')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            static::prescriptionSection(),
                            static::xraySection(),
                        ]),
                    Tab::make('Old Treatment Record')
                        ->icon('heroicon-o-archive-box')
                        ->schema([
                            static::oldTreatmentImagesSection(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function visitInformationSection(): Section
    {
        return Section::make('Visit Information')
            ->icon('heroicon-o-calendar-days')
            ->schema([
                Select::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->required(),
                Select::make('dentist_id')
                    ->label('Dentist')
                    ->relationship('dentist', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Dentist $record) => $record->user->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('visit_date')->default(today()),
                Select::make('appointment_id')
                    ->label('Appointment')
                    ->relationship('appointment', 'appointment_number')
                    ->searchable()
                    ->nullable(),
            ])
            ->columns(4)
            ->columnSpanFull();
    }

    protected static function clinicalAssessmentSection(): Section
    {
        return Section::make('Clinical Assessment')
            ->icon('heroicon-o-clipboard-document-check')
            ->schema([
                Textarea::make('chief_complaint')
                    ->rows(2)
                    ->columnSpanFull(),
                static::diagnosisSelect(),
                static::discountInput(),
                static::diagnosisTotalPlaceholder(),
                static::partialPaymentInput(),
                static::balanceDuePlaceholder(),
                Textarea::make('treatment_plan')->rows(4),
                Textarea::make('treatment_done')->rows(4),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    protected static function diagnosisSelect(): Select
    {
        return Select::make('diagnosis')
            ->label('Diagnosis')
            ->multiple()
            ->options(function (?DentalRecord $record) {
                $options = static::diagnosisOptions();

                // Keep previously saved diagnosis entries selectable even if the
                // matching service was since renamed, deactivated, or deleted
                // (or the diagnosis was free text from before this field became
                // service-linked) — otherwise they silently disappear on edit.
                foreach ($record?->diagnosisNames() ?? [] as $name) {
                    $options[$name] ??= $name;
                }

                return $options;
            })
            ->searchable()
            ->preload()
            ->live()
            ->columnSpanFull()
            // multiple() defaults to a JSON-array state cast, which json_decode()s the
            // stored comma-separated string into null (invalid JSON) and wipes the
            // selection before afterStateHydrated ever runs. Disable it so the raw
            // string reaches our own hydrate/dehydrate logic below untouched.
            ->stateCast(new class implements StateCast
            {
                public function get(mixed $state): mixed { return $state; }
                public function set(mixed $state): mixed { return $state; }
            })
            ->afterStateHydrated(function (Select $component, $state) {
                $component->state(filled($state) ? array_map('trim', explode(',', $state)) : []);
            })
            ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state);
    }

    protected static function diagnosisOptions(): array
    {
        return Service::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Service $service) => [
                $service->display_name => $service->display_name . ' — ₱' . number_format($service->price, 2),
            ])
            ->all();
    }

    protected static function discountInput(): TextInput
    {
        return TextInput::make('discount')
            ->label('Discount')
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->prefix('₱')
            ->live();
    }

    protected static function diagnosisTotalPlaceholder(): Placeholder
    {
        return Placeholder::make('diagnosis_total')
            ->label('Total')
            ->content(function (Get $get) {
                $selected = $get('diagnosis');
                $subtotal = Service::totalForDisplayNames(is_array($selected) ? $selected : []);
                $total = max(0, $subtotal - (float) ($get('discount') ?? 0));

                return '₱' . number_format($total, 2);
            });
    }

    protected static function partialPaymentInput(): TextInput
    {
        return TextInput::make('partial_payment')
            ->label('Partial Payment')
            ->helperText('Amount already collected from the patient for this visit, if any.')
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->prefix('₱')
            ->live()
            ->rule(fn (Get $get) => "max:" . max(0, Service::totalForDisplayNames(
                is_array($get('diagnosis')) ? $get('diagnosis') : []
            ) - (float) ($get('discount') ?? 0)))
            ->validationMessages([
                'max' => 'Partial payment cannot exceed the total.',
            ]);
    }

    protected static function balanceDuePlaceholder(): Placeholder
    {
        return Placeholder::make('balance_due')
            ->label('Balance Due')
            ->content(function (Get $get) {
                $selected = $get('diagnosis');
                $subtotal = Service::totalForDisplayNames(is_array($selected) ? $selected : []);
                $total = max(0, $subtotal - (float) ($get('discount') ?? 0));
                $balance = max(0, $total - (float) ($get('partial_payment') ?? 0));

                return '₱' . number_format($balance, 2);
            });
    }

    protected static function prescriptionSection(): Section
    {
        return Section::make('Prescription & Follow-up')
            ->icon('heroicon-o-clipboard-document-list')
            ->schema([
                Textarea::make('prescription')->rows(4),
                Textarea::make('notes')->rows(4),
                TextInput::make('next_visit_recommendation')
                    ->placeholder('e.g. Return in 2 weeks for follow-up')
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    protected static function xraySection(): Section
    {
        return Section::make('X-Ray Images')
            ->icon('heroicon-o-photo')
            ->schema([
                Repeater::make('xrays')
                    ->relationship()
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('X-Ray Image')
                            ->image()
                            ->directory('xrays'),
                        Select::make('xray_type')
                            ->label('X-Ray Type')
                            ->options(fn () => XrayType::where('is_active', true)->orderBy('sort_order')->pluck('name', 'name'))
                            ->searchable(),
                        Textarea::make('findings')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['xray_type'] ?? 'New X-Ray')
                    ->collapsible()
                    ->addActionLabel('Add X-Ray'),
            ])
            ->columnSpanFull();
    }

    protected static function oldTreatmentImagesSection(): Section
    {
        return Section::make('Old Treatment Record')
            ->icon('heroicon-o-archive-box')
            ->schema([
                Repeater::make('oldTreatmentImages')
                    ->relationship()
                    ->label('')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Image')
                            ->image()
                            ->directory('old-treatment-records'),
                        Textarea::make('note')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['note'] ?? 'New Image')
                    ->collapsible()
                    ->addActionLabel('Add Image'),
            ])
            ->columnSpanFull();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Visit Information')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('dentist.user.name')->label('Dentist'),
                    TextEntry::make('visit_date')->date(),
                    TextEntry::make('appointment.appointment_number')->label('Appointment')->placeholder('—'),
                ])->columns(4)->columnSpanFull(),

            Section::make('Clinical Assessment')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    TextEntry::make('chief_complaint')->label('Chief Complaint')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('diagnosis')->columnSpanFull(),
                    TextEntry::make('subtotal')->label('Subtotal')->money('PHP'),
                    TextEntry::make('discount')->label('Discount')->money('PHP'),
                    TextEntry::make('total')->label('Total')->money('PHP')->weight('bold'),
                    TextEntry::make('partial_payment')->label('Partial Payment')->money('PHP')->placeholder('—'),
                    TextEntry::make('balance_due')->label('Balance Due')->money('PHP')->weight('bold')
                        ->color(fn (DentalRecord $record) => $record->balance_due > 0 ? 'danger' : 'success')
                        ->columnSpanFull(),
                    TextEntry::make('treatment_plan')->label('Treatment Plan')->placeholder('—'),
                    TextEntry::make('treatment_done')->label('Treatment Done')->placeholder('—'),
                ])->columns(2)->columnSpanFull(),

            Section::make('Tooth Chart')
                ->icon('heroicon-o-squares-2x2')
                ->schema([
                    ViewEntry::make('tooth_chart')
                        ->label('')
                        ->view('filament.infolists.components.tooth-chart'),
                ])
                ->columnSpanFull(),

            Section::make('Prescription & Follow-up')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    TextEntry::make('prescription')->placeholder('—'),
                    TextEntry::make('notes')->placeholder('—'),
                    TextEntry::make('next_visit_recommendation')->label('Next Visit Recommendation')->placeholder('—')->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),

            Section::make('X-Ray Images')
                ->icon('heroicon-o-photo')
                ->schema([
                    RepeatableEntry::make('xrays')
                        ->schema([
                            ImageEntry::make('file_path')->label('Image'),
                            TextEntry::make('xray_type')->label('Type')->badge(),
                            TextEntry::make('findings')->placeholder('—'),
                        ])->columns(3),
                ])
                ->columnSpanFull()
                ->visible(fn (DentalRecord $record) => $record->xrays->isNotEmpty()),

            Section::make('Old Treatment Record')
                ->icon('heroicon-o-archive-box')
                ->schema([
                    RepeatableEntry::make('oldTreatmentImages')
                        ->label('')
                        ->schema([
                            ImageEntry::make('file_path')->label('Image'),
                            TextEntry::make('note')->placeholder('—'),
                        ])->columns(2),
                ])
                ->columnSpanFull()
                ->visible(fn (DentalRecord $record) => $record->oldTreatmentImages->isNotEmpty()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('has_invoice')
                    ->label('Invoice')
                    ->boolean()
                    ->getStateUsing(fn (DentalRecord $record): bool => $record->invoices()->exists())
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (bool $state): string => $state ? 'Invoice generated' : 'No invoice generated yet'),
                TextColumn::make('visit_date')->date()->sortable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                TextColumn::make('dentist.user.name')->label('Dentist')->searchable(),
                TextColumn::make('diagnosis')->limit(60)->searchable(),
                TextColumn::make('total')->label('Total')->money('PHP'),
                TextColumn::make('treatment_done')->limit(60),
                TextColumn::make('next_visit_recommendation')->limit(40),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('visit_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDentalRecords::route('/'),
            'create' => Pages\CreateDentalRecord::route('/create'),
            'view'   => Pages\ViewDentalRecord::route('/{record}'),
            'edit'   => Pages\EditDentalRecord::route('/{record}/edit'),
        ];
    }
}
