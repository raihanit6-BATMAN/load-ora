<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispatchFactory extends Factory
{
    protected $model = Dispatch::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(3, 25, 55);
        return [
            'dispatch_number' => 'DSP-' . date('Ymd') . '-' . fake()->unique()->numerify('####'),
            'client_id' => Client::factory(),
            'vehicle_id' => Vehicle::factory(),
            'driver_name' => fake()->name(),
            'goods_id' => Goods::factory(),
            'loading_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'loading_quantity' => $qty,
            'destination_location' => fake()->city(),
            'loading_point' => 'Quarry Main Yard',
            'status' => 'pending',
            'total_received_quantity' => 0.000,
            'total_deduction_quantity' => 0.000,
            'pending_quantity' => $qty,
            'note' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function received(): static
    {
        return $this->state(function (array $attributes) {
            $qty = $attributes['loading_quantity'] ?? 40.000;
            return [
                'status' => 'received',
                'total_received_quantity' => $qty,
                'total_deduction_quantity' => 0.000,
                'pending_quantity' => 0.000,
            ];
        });
    }
}
