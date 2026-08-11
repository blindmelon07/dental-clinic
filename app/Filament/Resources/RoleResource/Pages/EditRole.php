<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $selectedPermissionIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (Role $record) => in_array($record->name, ['super_admin', 'admin', 'dentist', 'receptionist', 'patient'])),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPermissionIds = RoleResource::extractPermissionIds($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions($this->selectedPermissionIds);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
