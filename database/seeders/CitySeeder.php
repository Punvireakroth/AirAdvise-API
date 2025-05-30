<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $majorCities = [
            [
                'name' => 'New York',
                'country' => 'United States',
                'region' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'population' => 8804190,
                'timezone' => 'America/New_York'
            ],
            [
                'name' => 'Los Angeles',
                'country' => 'United States',
                'region' => 'California',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'population' => 3898747,
                'timezone' => 'America/Los_Angeles'
            ],
            [
                'name' => 'London',
                'country' => 'United Kingdom',
                'region' => 'England',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'population' => 8982000,
                'timezone' => 'Europe/London'
            ],
            [
                'name' => 'Tokyo',
                'country' => 'Japan',
                'region' => 'Kanto',
                'latitude' => 35.6762,
                'longitude' => 139.6503,
                'population' => 13960000,
                'timezone' => 'Asia/Tokyo'
            ],
            [
                'name' => 'Beijing',
                'country' => 'China',
                'region' => 'Hebei',
                'latitude' => 39.9042,
                'longitude' => 116.4074,
                'population' => 21540000,
                'timezone' => 'Asia/Shanghai'
            ],
        ];

        // Insert cities in chunks
        foreach ($majorCities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'country' => $city['country']],
                $city
            );
        }
    }
}
