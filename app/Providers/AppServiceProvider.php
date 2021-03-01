<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Product;
use App\Observers\CustomerObserver;
use App\Observers\PlanObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Customer::observe(CustomerObserver::class);
        Plan::observe(PlanObserver::class);
        Product::observe(ProductObserver::class);
    }
}
