<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            // High intensity activities
            [
                'name' => 'Running',
                'intensity_level' => 'high',
                'description' => 'Fast-paced running or jogging',
                'active' => true
            ],
            [
                'name' => 'Cycling',
                'intensity_level' => 'high',
                'description' => 'Fast-paced cycling',
                'active' => true
            ],
            [
                'name' => 'Tennis',
                'intensity_level' => 'high',
                'description' => 'Tennis game or practice',
                'active' => true
            ],
            [
                'name' => 'Soccer',
                'intensity_level' => 'high',
                'description' => 'Soccer game or practice',
                'active' => true
            ],
            [
                'name' => 'Basketball',
                'intensity_level' => 'high',
                'description' => 'Basketball game or practice',
                'active' => true
            ],
            [
                'name' => 'HIIT workouts',
                'intensity_level' => 'high',
                'description' => 'High-intensity interval training',
                'active' => true
            ],

            // Moderate intensity activities
            [
                'name' => 'Brisk walking',
                'intensity_level' => 'moderate',
                'description' => 'Walking at a brisk pace',
                'active' => true
            ],
            [
                'name' => 'Light cycling',
                'intensity_level' => 'moderate',
                'description' => 'Casual cycling at moderate pace',
                'active' => true
            ],
            [
                'name' => 'Swimming',
                'intensity_level' => 'moderate',
                'description' => 'Swimming at a moderate pace',
                'active' => true
            ],
            [
                'name' => 'Yoga',
                'intensity_level' => 'moderate',
                'description' => 'Yoga session',
                'active' => true
            ],
            [
                'name' => 'Golf',
                'intensity_level' => 'moderate',
                'description' => 'Playing golf',
                'active' => true
            ],
            [
                'name' => 'Gardening',
                'intensity_level' => 'moderate',
                'description' => 'Gardening activities',
                'active' => true
            ],

            // Low intensity activities
            [
                'name' => 'Walking',
                'intensity_level' => 'low',
                'description' => 'Casual walking',
                'active' => true
            ],
            [
                'name' => 'Stretching',
                'intensity_level' => 'low',
                'description' => 'Light stretching exercises',
                'active' => true
            ],
            [
                'name' => 'Tai Chi',
                'intensity_level' => 'low',
                'description' => 'Tai Chi practice',
                'active' => true
            ],
            [
                'name' => 'Light gardening',
                'intensity_level' => 'low',
                'description' => 'Light gardening tasks',
                'active' => true
            ],
            [
                'name' => 'Casual strolling',
                'intensity_level' => 'low',
                'description' => 'Casual walking or strolling',
                'active' => true
            ]
        ];

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['name' => $activity['name'], 'intensity_level' => $activity['intensity_level']],
                $activity
            );
        }
    }
}
