<?php

namespace Database\Seeders;

use App\Models\Goods;
use Illuminate\Database\Seeder;

class GoodsSeeder extends Seeder
{
    public function run(): void
    {
        Goods::updateOrCreate(
            ['name' => 'STONE 10-20mm'],
            [
                'code' => 'STN-1020',
                'unit' => 'MT',
                'description' => 'Coarse aggregate 10mm to 20mm for concrete and road construction',
                'status' => 'active',
            ]
        );

        Goods::updateOrCreate(
            ['name' => 'STONE 5-10mm'],
            [
                'code' => 'STN-0510',
                'unit' => 'MT',
                'description' => 'Fine aggregate 5mm to 10mm chips',
                'status' => 'active',
            ]
        );
    }
}
