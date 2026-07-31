<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn (Model $model) => static::writeAuditLog($model, 'created', null, $model->auditableAttributes()));

        static::updated(function (Model $model) {
            $changes = collect($model->getChanges())
                ->except(array_merge(['updated_at'], $model->auditableExcluded()))
                ->keys()
                ->all();

            if (empty($changes)) {
                return;
            }

            $old = collect($model->getOriginal())->only($changes)->all();
            $new = collect($model->getAttributes())->only($changes)->all();

            static::writeAuditLog($model, 'updated', $old, $new);
        });

        static::deleted(fn (Model $model) => static::writeAuditLog($model, 'deleted', $model->auditableAttributes(), null));
    }

    protected static function writeAuditLog(Model $model, string $event, ?array $old, ?array $new): void
    {
        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'event' => $event,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'auditable_label' => $model->auditableLabel(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function auditableAttributes(): array
    {
        return collect($this->getAttributes())
            ->except(array_merge(['created_at', 'updated_at'], $this->auditableExcluded()))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function auditableExcluded(): array
    {
        return [];
    }

    public function auditableLabel(): string
    {
        foreach (['full_name', 'invoice_number', 'payment_number', 'appointment_number', 'prescription_number', 'name'] as $attribute) {
            if (filled($this->{$attribute} ?? null)) {
                return (string) $this->{$attribute};
            }
        }

        return class_basename($this) . ' #' . $this->getKey();
    }
}
