<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $selectedPermissionIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedPermissionIds = RoleResource::extractPermissionIds($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->selectedPermissionIds);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
