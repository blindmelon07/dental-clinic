<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CleaningRemindersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Dental Cleaning Reminders (Due within 30 days or Overdue)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Patient::query()
                    ->whereNotNull('next_cleaning_due')
                    ->where('next_cleaning_due', '<=', now()->addDays(30))
                    ->where('is_active', true)
                    ->orderBy('next_cleaning_due')
            )
            ->columns([
                TextColumn::make('patient_number')
                    ->label('Patient #')
                    ->copyable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Patient Name')
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('phone')
                    ->label('Phone'),

                TextColumn::make('next_cleaning_due')
                    ->label('Cleaning Due')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(fn (Patient $record): string =>
                        $record->next_cleaning_due->isPast() ? 'danger' : 'warning'
                    ),

                TextColumn::make('next_cleaning_due')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Patient $record): string =>
                        $record->next_cleaning_due->isPast()
                            ? 'Overdue by ' . $record->next_cleaning_due->diffForHumans(absolute: true)
                            : 'Due in ' . $record->next_cleaning_due->diffForHumans(absolute: true)
                    )
                    ->color(fn (Patient $record): string =>
                        $record->next_cleaning_due->isPast() ? 'danger' : 'warning'
                    ),
            ])
            ->emptyStateHeading('No upcoming cleaning reminders')
            ->emptyStateDescription('Patients with completed cleaning appointments will appear here 30 days before their 6-month due date.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
