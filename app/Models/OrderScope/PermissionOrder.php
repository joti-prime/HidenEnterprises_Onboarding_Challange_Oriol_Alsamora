<?php

namespace App\Models\OrderScope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PermissionOrder implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Default order application
        $builder->orderByRaw('`order` IS NULL, `order` ASC');
    }
}
