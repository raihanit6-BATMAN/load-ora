<?php

namespace Database\Factories;

use App\Models\Goods;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsFactory extends Factory
{
    protected $model = Goods::class;

    public function definition(): array
    {
        $material = fake()->randomElement(['STONE', 'SAND', 'CEMENT', 'AGGREGATE', 'GRAVEL', 'LIMESTONE', 'BALLAST']);
        $name = $material . ' ' . fake()->unique()->numerify('##-##mm-####');
        return [
            'name' => $name,
            'code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4) . fake()->unique()->numerify('##')),
            'unit' => 'MT',
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
