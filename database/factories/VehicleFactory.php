<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'vehicle_number' => strtoupper(fake()->bothify('??-##-????')),
            'vehicle_type' => fake()->randomElement(['10 Wheeler', '12 Wheeler', '14 Wheeler', 'Trailer', 'Dumper']),
            'driver_name' => fake()->name(),
            'driver_phone' => fake()->phoneNumber(),
            'capacity_mt' => fake()->randomFloat(3, 20, 60),
            'owner_type' => fake()->randomElement(['company_owned', 'hired', 'contractor']),
            'status' => 'active',
            'notes' => fake()->sentence(),
        ];
    }
}
