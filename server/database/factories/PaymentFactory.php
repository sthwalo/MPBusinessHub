<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'business_id' => Business::factory(),
            'package_id' => $this->faker->randomElement(['basic', 'premium', 'enterprise']),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'currency' => 'ZAR',
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'eft']),
            'transaction_id' => $this->faker->uuid,
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'cancelled']),
            'payfast_data' => null,
        ];
    }

    /**
     * Configure the model factory to indicate a pending payment.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'transaction_id' => null,
            ];
        });
    }

    /**
     * Configure the model factory to indicate a completed payment.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'transaction_id' => 'PF_' . time(),
            ];
        });
    }
}
