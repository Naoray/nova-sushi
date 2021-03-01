<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name,
            'stripe_id' => $this->faker->md5,
            'stripe_status' => $this->faker->word,
            'quantity' => $this->faker->randomNumber,
            'trial_ends_at' => $this->faker->dateTime,
        ];
    }
}
