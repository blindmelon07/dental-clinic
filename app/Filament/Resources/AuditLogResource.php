<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin']) ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Audit Entry')
                ->schema([
                    TextEntry::make('created_at')->label('When')->dateTime('M d, Y g:i A'),
                    TextEntry::make('user_name')->label('User'),
                    TextEntry::make('event')->badge()->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                    TextEntry::make('model_label')->label('Model'),
                    TextEntry::make('auditable_label')->label('Record'),
                ])->columns(3),

            Section::make('Before')
                ->schema([
                    TextEntry::make('old_values')
                        ->label('')
                        ->state(fn (AuditLog $record) => static::formatValues($record->old_values))
                        ->html(),
                ])
                ->visible(fn (AuditLog $record) => filled($record->old_values)),

            Section::make('After')
                ->schema([
                    TextEntry::make('new_values')
                        ->label('')
                        ->state(fn (AuditLog $record) => static::formatValues($record->new_values))
                        ->html(),
                ])
                ->visible(fn (AuditLog $record) => filled($record->new_values)),
        ]);
    }

    protected static function formatValues(?array $values): string
    {
        if (blank($values)) {
            return '<span class="text-gray-400">—</span>';
        }

        $rows = collect($values)->map(function ($value, $key) {
            $display = is_array($value) ? json_encode($value) : (string) $value;

            return '<div style="padding:2px 0"><span style="font-weight:600">' . e($key) . ':</span> ' . e($display) . '</div>';
        })->implode('');

        return $rows;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('M d, Y g:i A')->sortable(),
                TextColumn::make('user_name')->label('User')->searchable(),
                TextColumn::make('event')->badge()->color(fn (string $state): string => match ($state) {
                    'created' => 'success',
                    'updated' => 'info',
                    'deleted' => 'danger',
                    default => 'gray',
                })->sortable(),
                TextColumn::make('model_label')->label('Model')->badge()->color('gray'),
                TextColumn::make('auditable_label')->label('Record')->searchable()->limit(50),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),

                SelectFilter::make('auditable_type')
                    ->label('Model')
                    ->options(fn () => AuditLog::query()
                        ->distinct()
                        ->pluck('auditable_type', 'auditable_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->sort()
                        ->all()),

                Filter::make('created_at')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
