<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // \Stripe\Stripe::setMaxNetworkRetries(2);

        $owner = User::factory()->create(['email' => 'test@owner.test']);

        $product1 = Product::factory()->create();
        $plan = Plan::factory()->create([
            'id' => 'plan-500-monthly',
            'product' => $product1,
            'amount' => 4900,
            'interval' => 'month',
        ]);
        Plan::factory()->create([
            'id' => 'plan1-500-yearly',
            'product' => $product1,
            'amount' => 49000,
            'interval' => 'year',
        ]);
        Subscription::factory()->create([
            'name' => $plan->name,
            'user_id' => $owner,
        ]);
    }
}
