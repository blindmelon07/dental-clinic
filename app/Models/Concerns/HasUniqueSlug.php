<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    /**
     * Slugify $name and, if that slug is already taken (e.g. by another record,
     * or a pre-seeded row), append -2, -3, ... until a free one is found.
     *
     * Pass $ignoreId (the record's own id) when regenerating a slug on update,
     * so the record isn't seen as colliding with itself.
     */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
