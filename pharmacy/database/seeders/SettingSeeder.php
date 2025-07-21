<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            ['app_name' => 'PMS'],
            ['title' => 'Pharmacy Management System'],
            ['phone' => '+000000000'],
            ['email' => 'info@example.com'],
            ['favicon' => 'images/favicon.png'],
            ['logo' => 'images/logo.png'],
            ['default_language' => 'en'],
            ['currency' => '$'],
            ['time_zone' => 'asia/dhaka'],
        ];
    }
}
