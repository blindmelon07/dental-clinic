<?php

namespace App\Filament\Pages;

use App\Mail\WelcomeMail;
use App\Models\Patient;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PatientApprovals extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static string|\UnitEnum|null $navigationGroup = 'Clinic Operations';
    protected static ?string $navigationLabel = 'Patient Approvals';
    protected static ?int $navigationSort = 0;
    protected string $view = 'filament.pages.patient-approvals';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_patient') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::pendingQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    protected static function pendingQuery(): Builder
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'patient'))
            ->where('is_active', false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(static::pendingQuery())
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('created_at')->label('Registered')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('This will create the patient record and let them book appointments immediately.')
                    ->action(function (User $record) {
                        $data = $record->pending_patient_data;
                        $hasExistingPatient = $record->patient()->exists();

                        if (empty($data) && ! $hasExistingPatient) {
                            Notification::make()
                                ->title('Cannot approve')
                                ->body('No registration data found for ' . $record->name . '.')
                                ->danger()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($record, $data, $hasExistingPatient) {
                            if (! $hasExistingPatient) {
                                Patient::create([
                                    ...$data,
                                    'user_id'        => $record->id,
                                    'clinic_id'      => $record->clinic_id,
                                    'patient_number' => 'PT-' . date('Ymd') . '-' . str_pad(
                                        Patient::whereDate('created_at', today())->count() + 1,
                                        4, '0', STR_PAD_LEFT
                                    ),
                                ]);
                            }

                            $record->update([
                                'is_active'             => true,
                                'pending_patient_data'  => null,
                            ]);
                        });

                        try {
                            $record->load('patient');
                            Mail::to($record->email)->send(new WelcomeMail($record));
                        } catch (\Throwable $e) {
                            Log::error('Approval WelcomeMail failed: ' . $e->getMessage(), ['user_id' => $record->id]);
                        }

                        Notification::make()
                            ->title('Patient approved')
                            ->body($record->name . ' can now book appointments and was emailed a welcome message.')
                            ->success()
                            ->send();
                    }),
                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove registration')
                    ->modalDescription('This permanently deletes this pending sign-up. They will need to register again if they want an account.')
                    ->action(function (User $record) {
                        $name = $record->name;
                        $record->delete();

                        Notification::make()
                            ->title('Registration removed')
                            ->body($name . '\'s pending registration was removed.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No pending registrations')
            ->emptyStateDescription('New patient sign-ups awaiting approval will appear here.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->defaultSort('created_at', 'desc');
    }
}
