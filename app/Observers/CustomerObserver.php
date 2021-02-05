<?php

namespace App\Observers;

use App\Models\Customer;
use Mollie\Laravel\Facades\Mollie;

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
        Mollie::api()->customers()->create([
            'name'  => $customer->name,
            'email' => $customer->email,
        ]);
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
        Mollie::api()->customers()->delete($customer->id);
    }
}
