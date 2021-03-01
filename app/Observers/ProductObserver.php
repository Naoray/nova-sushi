<?php

namespace App\Observers;

use App\Models\Product;
use Exception;
use Laravel\Cashier\Cashier;
use Stripe\Product as StripeProduct;

class ProductObserver
{
    /**
     * Handle the product "creating" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function creating(Product $product)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        try {
            /*
             * We need to assign an Identifier for this model. Otherwise, Nova
             * will throw an 'Integrity constraint violation' Exception due to
             * the missing value for the 'action_events.actionable_id' which is
             * derived from the Nova resource Model which is created.
             */
            $product->id = StripeProduct::create($product->toConsumableArray(), Cashier::stripeOptions())['id'];
        } catch (Exception $e) {
        }
    }

    /**
     * Handle the product "updating" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updating(Product $product)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $attributes = collect($product->getDirty())
            ->filter(fn ($value, $key) => in_array($key, Product::$updateable))
            ->all();

        if (!empty($attributes)) {
            StripeProduct::update($product->id, $attributes, Cashier::stripeOptions());
        }
    }

    /**
     * Handle the product "deleting" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function deleting(Product $product)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StripeProduct::retrieve($product->id, Cashier::stripeOptions())->delete();
    }
}
