<?php

namespace App\Filament\Concerns;

use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trash management (view / restore / permanently delete) for resources whose
 * model uses SoftDeletes.
 *
 * There are no model policies in this app, and Filament's actions authorize
 * through the *AuthorizationResponse() methods (not the resources' can*()
 * overrides) — with no policy those fall back to "allow". So the gates live here:
 *   - delete / restore → the resource's existing delete_{key} permission
 *   - permanent delete → super_admin only, and only once nothing references the
 *     record any more: the foreign keys cascade, so a force delete would
 *     silently wipe those rows too (trashed ones included).
 */
trait ManagesTrashedRecords
{
    /** Permission suffix, e.g. 'patient' → delete_patient. */
    abstract protected static function trashPermissionKey(): string;

    /**
     * Tables (=> foreign key) whose rows would be cascade-deleted along with the
     * record. Any matching row — trashed or not — blocks a permanent delete.
     */
    protected static function forceDeleteDependents(): array
    {
        return [];
    }

    // Let view/edit pages (and their restore actions) open trashed records.
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        return static::trashPermissionResponse();
    }

    public static function getDeleteAnyAuthorizationResponse(): Response
    {
        return static::trashPermissionResponse();
    }

    public static function getRestoreAuthorizationResponse(Model $record): Response
    {
        return static::trashPermissionResponse();
    }

    public static function getRestoreAnyAuthorizationResponse(): Response
    {
        return static::trashPermissionResponse();
    }

    public static function getForceDeleteAuthorizationResponse(Model $record): Response
    {
        if (! static::canManageTrash()) {
            return Response::deny('Only super admins can permanently delete records.');
        }

        foreach (static::forceDeleteDependents() as $table => $foreignKey) {
            if (DB::table($table)->where($foreignKey, $record->getKey())->exists()) {
                return Response::deny('Still has ' . Str::headline($table) . ' — delete those permanently first.');
            }
        }

        return Response::allow();
    }

    public static function getForceDeleteAnyAuthorizationResponse(): Response
    {
        return static::canManageTrash()
            ? Response::allow()
            : Response::deny('Only super admins can permanently delete records.');
    }

    public static function trashFilter(): TrashedFilter
    {
        return TrashedFilter::make();
    }

    /** Row / header actions; each shows only on trashed records. */
    public static function trashActions(): array
    {
        return [
            RestoreAction::make(),
            ForceDeleteAction::make()
                ->hidden(fn () => ! static::canManageTrash())
                ->authorizationTooltip(),
        ];
    }

    public static function trashBulkActions(): array
    {
        return [
            RestoreBulkAction::make(),
            ForceDeleteBulkAction::make()
                ->hidden(fn () => ! static::canManageTrash())
                ->authorizeIndividualRecords(),
        ];
    }

    protected static function trashPermissionResponse(): Response
    {
        return auth()->user()?->can('delete_' . static::trashPermissionKey())
            ? Response::allow()
            : Response::deny('You do not have permission to delete or restore these records.');
    }

    protected static function canManageTrash(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }
}
