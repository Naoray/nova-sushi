<?php

namespace App\Observers;

use App\Models\Plan;
use Exception;
use Laravel\Cashier\Cashier;
use Stripe\Plan as StripePlan;

class PlanObserver
{
    /**
     * Handle the plan "creating" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function creating(Plan $plan)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        try {
            StripePlan::create($plan->toConsumableArray(), Cashier::stripeOptions());
        } catch (Exception $e) {
        }
    }

    /**
     * Handle the plan "updating" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function updating(Plan $plan)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $attributes = $plan->getUpdatetableAttributes();

        if (!empty($attributes)) {
            StripePlan::update($plan->id, $attributes, Cashier::stripeOptions());
        }
    }

    /**
     * Handle the plan "deleting" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function deleting(Plan $plan)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StripePlan::retrieve($plan->id, Cashier::stripeOptions())->delete();
    }
}
