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

        $cambodianCities = [
            [
                'name' => 'Phnom Penh',
                'country' => 'Cambodia',
                'region' => 'Capital',
                'latitude' => 11.5564,
                'longitude' => 104.9282,
                'population' => 2129371,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Siem Reap',
                'country' => 'Cambodia',
                'region' => 'Siem Reap',
                'latitude' => 13.3633,
                'longitude' => 103.8561,
                'population' => 139458,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Battambang',
                'country' => 'Cambodia',
                'region' => 'Battambang',
                'latitude' => 13.0980,
                'longitude' => 103.1995,
                'population' => 180853,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kampong Cham',
                'country' => 'Cambodia',
                'region' => 'Kampong Cham',
                'latitude' => 12.0005,
                'longitude' => 105.4600,
                'population' => 118242,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kampong Chhnang',
                'country' => 'Cambodia',
                'region' => 'Kampong Chhnang',
                'latitude' => 12.2505,
                'longitude' => 104.6667,
                'population' => 46380,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kampong Speu',
                'country' => 'Cambodia',
                'region' => 'Kampong Speu',
                'latitude' => 11.4522,
                'longitude' => 104.5200,
                'population' => 35747,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kampong Thom',
                'country' => 'Cambodia',
                'region' => 'Kampong Thom',
                'latitude' => 12.7111,
                'longitude' => 104.8887,
                'population' => 31871,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kampot',
                'country' => 'Cambodia',
                'region' => 'Kampot',
                'latitude' => 10.6100,
                'longitude' => 104.1800,
                'population' => 38604,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kandal',
                'country' => 'Cambodia',
                'region' => 'Kandal',
                'latitude' => 11.4864,
                'longitude' => 104.9307,
                'population' => 35093,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kep',
                'country' => 'Cambodia',
                'region' => 'Kep',
                'latitude' => 10.4833,
                'longitude' => 104.3167,
                'population' => 40280,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Koh Kong',
                'country' => 'Cambodia',
                'region' => 'Koh Kong',
                'latitude' => 11.6167,
                'longitude' => 102.9833,
                'population' => 36053,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Kratie',
                'country' => 'Cambodia',
                'region' => 'Kratie',
                'latitude' => 12.4900,
                'longitude' => 106.0300,
                'population' => 38215,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Mondulkiri',
                'country' => 'Cambodia',
                'region' => 'Mondulkiri',
                'latitude' => 12.7875,
                'longitude' => 107.1014,
                'population' => 60811,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Oddar Meanchey',
                'country' => 'Cambodia',
                'region' => 'Oddar Meanchey',
                'latitude' => 14.1667,
                'longitude' => 103.5000,
                'population' => 185819,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Pailin',
                'country' => 'Cambodia',
                'region' => 'Pailin',
                'latitude' => 12.8490,
                'longitude' => 102.6055,
                'population' => 70482,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Preah Sihanouk',
                'country' => 'Cambodia',
                'region' => 'Preah Sihanouk',
                'latitude' => 10.6100,
                'longitude' => 103.5300,
                'population' => 89447,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Preah Vihear',
                'country' => 'Cambodia',
                'region' => 'Preah Vihear',
                'latitude' => 13.8000,
                'longitude' => 104.9833,
                'population' => 171139,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Prey Veng',
                'country' => 'Cambodia',
                'region' => 'Prey Veng',
                'latitude' => 11.4900,
                'longitude' => 105.3300,
                'population' => 59060,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Pursat',
                'country' => 'Cambodia',
                'region' => 'Pursat',
                'latitude' => 12.5338,
                'longitude' => 103.9144,
                'population' => 51120,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Ratanakiri',
                'country' => 'Cambodia',
                'region' => 'Ratanakiri',
                'latitude' => 13.7500,
                'longitude' => 107.0000,
                'population' => 184000,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Stung Treng',
                'country' => 'Cambodia',
                'region' => 'Stung Treng',
                'latitude' => 13.5258,
                'longitude' => 105.9708,
                'population' => 29667,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Svay Rieng',
                'country' => 'Cambodia',
                'region' => 'Svay Rieng',
                'latitude' => 11.0877,
                'longitude' => 105.8003,
                'population' => 24929,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Takeo',
                'country' => 'Cambodia',
                'region' => 'Takeo',
                'latitude' => 10.9900,
                'longitude' => 104.7800,
                'population' => 39186,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Tboung Khmum',
                'country' => 'Cambodia',
                'region' => 'Tboung Khmum',
                'latitude' => 11.8891,
                'longitude' => 105.8764,
                'population' => 754000,
                'timezone' => 'Asia/Phnom_Penh'
            ],
            [
                'name' => 'Banteay Meanchey',
                'country' => 'Cambodia',
                'region' => 'Banteay Meanchey',
                'latitude' => 13.7531,
                'longitude' => 102.9896,
                'population' => 678033,
                'timezone' => 'Asia/Phnom_Penh'
            ]
        ];

        // Insert existing major cities
        foreach ($majorCities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'country' => $city['country']],
                $city
            );
        }

        // Insert Cambodian cities
        foreach ($cambodianCities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'country' => $city['country']],
                $city
            );
        }
    }
}