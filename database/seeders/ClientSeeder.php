<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::updateOrCreate(
            ['name' => 'P-Block Project'],
            [
                'code' => 'P-BLK',
                'company_name' => 'P-Block Construction & Development',
                'contact_person' => 'Site Operations Engineer',
                'phone' => '+8801800000001',
                'delivery_location' => 'P-Block Site',
                'status' => 'active',
                'notes' => 'Primary destination site for stone aggregate dispatches',
            ]
        );

        Client::updateOrCreate(
            ['name' => 'General / Direct Party'],
            [
                'code' => 'DIR-PARTY',
                'company_name' => 'Direct Commercial Dispatches',
                'contact_person' => 'Logistics Desk',
                'status' => 'active',
                'notes' => 'General party for ad-hoc dispatches',
            ]
        );
    }
}
