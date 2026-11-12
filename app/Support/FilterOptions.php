<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilterOptions
{
    /** Return only values referenced by the access-scoped records, before pagination. */
    public static function from(Builder $records, string $model, string $column): Collection
    {
        $ids = (clone $records)->withoutEagerLoads()->reorder()->select($column);

        return $model::whereIn('id', $ids)->orderBy('name')->get();
    }
}
