<?php

namespace App\Models;

use League\Fractal\TransformerAbstract;

trait HasMollieAccess
{
    abstract protected function transformer(): TransformerAbstract;

    public static function bootHasMollieAccess()
    {
        static::addGlobalScope('null-id', function ($builder) {
            $builder->whereKeyNot(null);
        });
    }

    protected function transformToArray($data): array
    {
        $transformed = collect($data)
            ->transformWith($this->transformer())
            ->toArray();

        if (empty($transformed)) {
            $transformed = [array_fill_keys($this->fillable, null)];
        }

        return $transformed;
    }
}
