<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create(['email' => 'admin@test.com'])->createAsStripeCustomer();

        collect()->range(1, 5)->each(fn () => User::factory()->create()->createAsStripeCustomer());
    }
}
