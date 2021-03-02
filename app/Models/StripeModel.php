<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Cashier;
use Stripe\Collection;

abstract class StripeModel extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    public static function booted()
    {
        static::addGlobalScope('null-id', function (Builder $builder) {
            $builder->whereKeyNot(null);
        });
    }

    /**
     * Returns all keys of the stripe object.
     *
     * @return array
     */
    abstract public static function keys(): array;

    public function getConvertedRows(string $apiResource)
    {
        return $this->transformToArray($apiResource::all(['limit' => 100], Cashier::stripeOptions()));
    }

    /**
     * Transforms stripe data responses into arrays.
     */
    protected function transformToArray(Collection $stripeData): array
    {
        $transformed = collect($stripeData->data)
            ->map(function ($item) {
                return collect($item->toArray())
                    ->map(fn ($value, $key) => $this->castStripeData($key, $value))
                    ->all();
            })->all();

        if (empty($transformed)) {
            $transformed = [array_fill_keys(static::keys(), null)];
        }

        return $transformed;
    }

    /**
     * Cast incoming stripe data into sqlite conformable data.
     *
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
    protected function castStripeData($key, $value)
    {
        if (is_array($value)) {
            $value = empty($value) ? null : $this->castAttributeAsJson($key, $value);
        } elseif (array_key_exists($key, $this->casts)) {
            $value = $this->castAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Converts all data objects into arrays to make them
     * consumable by stripes API.
     *
     * @return array
     */
    public function toConsumableArray(): array
    {
        return collect($this->toArray())
            ->all();
    }

    public function getUpdatetableAttributes(): array
    {
        return  collect($this->getDirty())
            ->filter(fn ($value, $key) => in_array($key, self::$updateable))
            ->all();
    }
}
