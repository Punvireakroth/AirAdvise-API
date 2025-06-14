<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert the specific location data
        DB::table('locations')->insert([
            'id' => 57,
            'city_name' => 'Unknown Location',
            'state_province' => null,
            'country' => 'Unknown',
            'country_code' => null,
            'latitude' => 37.42199830,
            'longitude' => -122.08400000,
            'timezone' => null,
            'is_active' => 1,
            'created_at' => '2025-05-10 04:24:24',
            'updated_at' => '2025-05-10 04:24:24',
        ]);

        // Example of adding another location:
        /*
        DB::table('locations')->insert([
            'city_name' => 'San Francisco',
            'state_province' => 'California',
            'country' => 'United States',
            'country_code' => 'US',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'timezone' => 'America/Los_Angeles',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        */
    }
}