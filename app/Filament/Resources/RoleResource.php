<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Roles & Permissions';

    public static function canViewAny(): bool { return auth()->user()?->hasRole('super_admin'); }
    public static function canCreate(): bool  { return auth()->user()?->hasRole('super_admin'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $r): bool   { return auth()->user()?->hasRole('super_admin'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $r): bool { return auth()->user()?->hasRole('super_admin'); }

    public static function form(Schema $schema): Schema
    {
        // Group permissions by resource for easier reading
        $groups = self::groupedPermissions();

        return $schema->components([
            Section::make('Role Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->helperText('Use lowercase with underscores, e.g. clinic_manager'),
                ])->columns(1),

            ...collect($groups)->map(function (array $permissions, string $group) {
                return Section::make($group)
                    ->schema([
                        CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->options(
                                collect($permissions)
                                    ->mapWithKeys(fn ($p) => [$p->id => self::formatPermissionLabel($p->name)])
                            )
                            ->columns(2)
                            ->gridDirection('row'),
                    ])
                    ->collapsible()
                    ->collapsed(false);
            })->values()->all(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Role Details')
                ->schema([
                    TextEntry::make('name')->badge(),
                    TextEntry::make('permissions_count')
                        ->label('Total Permissions')
                        ->state(fn (Role $record) => $record->permissions->count()),
                ])->columns(2),

            Section::make('Assigned Permissions')
                ->schema([
                    TextEntry::make('permissions.name')
                        ->label('')
                        ->badge()
                        ->separator(',')
                        ->formatStateUsing(fn ($state) => self::formatPermissionLabel($state))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->badge()->searchable()->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Role $record) => in_array($record->name, ['super_admin', 'admin', 'dentist', 'receptionist', 'patient'])),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view'   => Pages\ViewRole::route('/{record}'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────

    private static function groupedPermissions(): array
    {
        $groups = [];

        foreach (Permission::orderBy('name')->get() as $permission) {
            // Derive group from permission name: e.g. "view_any_dental_record" → "Dental Record"
            $parts = explode('_', $permission->name);
            // Last word(s) form the resource name; strip action prefix (view_any, view, create, update, delete)
            $resource = self::permissionGroup($permission->name);
            $groups[$resource][] = $permission;
        }

        ksort($groups);

        return $groups;
    }

    private static function permissionGroup(string $name): string
    {
        $map = [
            'patient'      => 'Patients',
            'appointment'  => 'Appointments',
            'dental_record'=> 'Dental Records',
            'invoice'      => 'Invoices',
            'service'      => 'Services',
            'dentist'      => 'Dentists',
            'user'         => 'Users',
            'prescription' => 'Prescriptions',
        ];

        foreach ($map as $key => $label) {
            if (str_contains($name, $key)) {
                return $label;
            }
        }

        return 'Other';
    }

    public static function formatPermissionLabel(string $name): string
    {
        $actionMap = [
            'view_any_' => 'View List',
            'view_'     => 'View',
            'create_'   => 'Create',
            'update_'   => 'Edit',
            'delete_'   => 'Delete',
        ];

        foreach ($actionMap as $prefix => $label) {
            if (str_starts_with($name, $prefix)) {
                return $label;
            }
        }

        return ucwords(str_replace('_', ' ', $name));
    }
}
