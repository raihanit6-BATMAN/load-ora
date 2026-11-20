<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name' => $name,
            'code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4) . fake()->unique()->numerify('##')),
            'company_name' => $name . ' Ltd.',
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'billing_address' => fake()->address(),
            'delivery_location' => fake()->city(),
            'status' => 'active',
            'notes' => fake()->sentence(),
        ];
    }
}
