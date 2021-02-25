<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Stripe\Collection;

trait HasDirectStripeAccess
{
    /**
     * Returns all keys of the stripe object.
     */
    abstract protected function stripeKeys(): array;

    /**
     * Boot the has direct Stripe access for a model.
     * This global scope hides the entries we have to
     * create to prevent a Sushi error which always requires
     * at least one row if no Stripe API data is found initially.
     *
     * @return void
     */
    public static function bootHasDirectStripeAccess()
    {
        static::addGlobalScope('null-id', function (Builder $builder) {
            $builder->whereKeyNot(null);
        });
    }

    /**
     * Transforms stripe data responses into arrays.
     */
    protected function transformToArray(Collection $stripeData): array
    {
        return Cache::remember(get_class($this), Carbon::parse('5 Minutes'), function () use ($stripeData) {
            $transformed = collect($stripeData->autoPagingIterator())->map(function ($item) {
                return collect($item->toArray())
                    ->map(function ($value, $key) {
                        if (is_array($value)) {
                            $value = empty($value) ? null : $this->castAttributeAsJson($key, $value);
                        } elseif (array_key_exists($key, $this->casts)) {
                            $value = $this->castAttribute($key, $value);
                        }

                        return $value;
                    })
                    ->all();
            })->all();

            if (empty($transformed)) {
                $transformed = [array_fill_keys($this->stripeKeys(), null)];
            }

            return $transformed;
        });
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
            ->map(function ($value, $key) {
                return $this->castObjectToArray($value, $key);
            })
            ->all();
    }

    public function getConsumableAttribute(string $key)
    {
        return $this->castObjectToArray(
            $this->getAttribute($key),
            $key
        );
    }

    private function castObjectToArray($value, string $key)
    {
        if (is_object($value) && array_key_exists($key, $this->casts)) {
            $value = (array)$value;
        }

        return $value;
    }
}
