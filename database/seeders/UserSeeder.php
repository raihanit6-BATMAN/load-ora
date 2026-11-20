<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@loadora.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+8801700000001',
            ]
        );
    }
}
