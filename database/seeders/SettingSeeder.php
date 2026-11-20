<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app.name', 'value' => 'LOADORA', 'group' => 'general', 'type' => 'string', 'is_system' => true],
            ['key' => 'app.company_name', 'value' => 'Loadora Logistics & Dispatch Management', 'group' => 'general', 'type' => 'string', 'is_system' => false],
            ['key' => 'app.quantity_unit', 'value' => 'MT', 'group' => 'dispatch', 'type' => 'string', 'is_system' => true],
            ['key' => 'app.date_format', 'value' => 'd/m/Y', 'group' => 'general', 'type' => 'string', 'is_system' => false],
            ['key' => 'app.theme', 'value' => 'light', 'group' => 'general', 'type' => 'string', 'is_system' => false],
            ['key' => 'app.dispatch_prefix', 'value' => 'DSP', 'group' => 'dispatch', 'type' => 'string', 'is_system' => false],
            ['key' => 'app.alert_pending_hours', 'value' => '48', 'group' => 'dispatch', 'type' => 'integer', 'is_system' => false],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
