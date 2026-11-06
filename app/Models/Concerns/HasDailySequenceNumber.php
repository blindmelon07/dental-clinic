<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;

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

    /**
     * Run $create with a freshly generated number, retrying with a new number
     * when the insert hits the column's unique index. nextDailyNumber() is a
     * read-then-insert, so two concurrent requests (e.g. a double-clicked Save)
     * can both compute the same number; the loser simply tries again.
     *
     * @template T
     * @param  callable(): string  $generate  produces a candidate number
     * @param  callable(string): T  $create  receives the number to use
     * @return T
     */
    protected static function retryOnDuplicateNumber(string $column, callable $generate, callable $create, int $maxAttempts = 5): mixed
    {
        $indexName = (new static)->getTable() . '_' . $column . '_unique';

        for ($attempt = 1; ; $attempt++) {
            try {
                return $create($generate());
            } catch (QueryException $e) {
                $isDuplicateNumber = (int) $e->getCode() === 23000
                    && str_contains($e->getMessage(), $indexName);

                if (! $isDuplicateNumber || $attempt >= $maxAttempts) {
                    throw $e;
                }
            }
        }
    }
}
