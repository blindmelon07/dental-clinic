<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HasDailySequenceNumber
{
    /**
     * Next "{PREFIX}{Ymd}-{0001}" number for today.
     *
     * Bases the sequence on the highest existing suffix for today (including
     * soft-deleted rows, since their number still occupies the unique slot).
     * Counting rows undercounts after any delete and produces collisions.
     */
    protected static function nextDailyNumber(string $prefix, string $column): string
    {
        $prefix .= date('Ymd') . '-';

        $query = in_array(SoftDeletes::class, class_uses_recursive(static::class))
            ? static::withTrashed()
            : static::query();

        $lastSequence = $query
            ->where($column, 'like', $prefix . '%')
            ->pluck($column)
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max();

        return $prefix . str_pad((int) $lastSequence + 1, 4, '0', STR_PAD_LEFT);
    }
}
