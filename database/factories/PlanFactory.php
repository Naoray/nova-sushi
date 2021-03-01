<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $interval = ['month', 'year'];

        return [
            'active' => true,
            'amount' => 2000,
            'currency' => 'eur',
            'interval' => $interval[array_rand($interval)],
            'product' => Product::factory()->lazy(),
        ];
    }

    public function withProduct()
    {
        return $this->state(function () {
            return [
                'product' => $this->faker->uuid,
            ];
        });
    }
}
