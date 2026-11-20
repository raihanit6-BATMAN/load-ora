<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        $received = fake()->randomFloat(3, 20, 50);
        $deduction = fake()->randomFloat(3, 0, 0.5);
        return [
            'dispatch_id' => Dispatch::factory(),
            'receiving_date' => fake()->dateTimeBetween('-25 days', 'now')->format('Y-m-d'),
            'received_quantity' => $received,
            'deduction_quantity' => $deduction,
            'unloading_location' => fake()->city(),
            'receipt_status' => fake()->randomElement(['received', 'shortage', 'disputed']),
            'remarks' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
