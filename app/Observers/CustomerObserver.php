<?php

namespace App\Observers;

use App\Models\Customer;
use Laravel\Cashier\Cashier;
use Stripe\Customer as StripeCustomer;

class CustomerObserver
{
    /**
     * Handle the Customer "creating" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function creating(Customer $customer)
    {
        StripeCustomer::create($customer->toConsumableArray(), Cashier::stripeOptions());
    }

    /**
     * Handle the Customer "updated" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function updated(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "deleting" event.
     *
     * @param  \App\Models\Customer  $customer
     * @return void
     */
    public function deleting(Customer $customer)
    {
        StripeCustomer::retrieve($customer->id, Cashier::stripeOptions())->delete();
    }
}
