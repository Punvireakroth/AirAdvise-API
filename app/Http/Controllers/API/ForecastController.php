<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AirQualityForecast;
use App\Models\Location;
use App\Services\ForecastAirQualityApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ForecastController extends Controller
{
    protected $forecastService;

    public function __construct(ForecastAirQualityApiService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * Get forecasts for a specific location
     */
    public function getByLocation(Location $location, Request $request)
    {
        Log::info('getByLocation', ['location' => $location, 'request' => $request->all()]);

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::today();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::today()->addDays(6);

        // Validate range (max 7 days)
        if ($startDate->diffInDays($endDate) > 6) {
            $endDate = $startDate->copy()->addDays(6);
        }

        // Try to get from database first
        $forecasts = $this->getForecasts($location, $startDate, $endDate);

        // Always calculate best day, regardless of whether activity_type is provided
        $bestDay = $this->calculateBestDay($forecasts);

        return response()->json([
            'forecasts' => $forecasts,
            'best_day' => $bestDay,
        ]);
    }

    /**
     * Get forecasts, fetching from external API if needed
     */
    protected function getForecasts(Location $location, Carbon $startDate, Carbon $endDate)
    {
        // Check if we have forecasts in our database
        $existingForecasts = AirQualityForecast::where('location_id', $location->id)
            ->whereBetween('forecast_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // If we have all days, return them
        if ($existingForecasts->count() == $startDate->diffInDays($endDate) + 1) {
            return $existingForecasts;
        }

        // Otherwise, fetch from external API and store
        $externalForecasts = $this->forecastService->getForecast($location->latitude, $location->longitude);

        if ($externalForecasts) {
            $savedForecasts = [];

            foreach ($externalForecasts as $forecastData) {
                $forecastDate = Carbon::parse($forecastData['forecast_date']);

                // Only save if within our requested range
                if ($forecastDate->between($startDate, $endDate)) {
                    $forecast = AirQualityForecast::updateOrCreate(
                        [
                            'location_id' => $location->id,
                            'forecast_date' => $forecastData['forecast_date'],
                        ],
                        $forecastData
                    );

                    $savedForecasts[] = $forecast;
                }
            }

            return collect($savedForecasts);
        }

        return $existingForecasts;
    }

    /**
     * Calculate best day and recommended activities
     */
    protected function calculateBestDay($forecasts)
    {
        if ($forecasts->isEmpty()) {
            return null;
        }

        // Get the day with the lowest AQI
        $bestDay = $forecasts->sortBy('aqi')->first();

        // Clone the best day to avoid modifying the original object
        $bestDayWithActivities = clone $bestDay;

        // Get recommended activities based on AQI level
        $recommendedActivities = $this->getRecommendedActivities($bestDay->aqi);

        // Add the recommendations to the best day data
        $bestDayWithActivities->recommended_activities = $recommendedActivities;

        Log::info('Best day calculated', [
            'best_day' => $bestDayWithActivities,
            'aqi' => $bestDay->aqi
        ]);

        return $bestDayWithActivities;
    }

    /**
     * Get recommended activities based on AQI level
     */
    protected function getRecommendedActivities($aqi)
    {
        $activities = [
            'high' => [
                'Running',
                'Cycling',
                'Tennis',
                'Soccer',
                'Basketball',
                'HIIT workouts'
            ],
            'moderate' => [
                'Brisk walking',
                'Light cycling',
                'Swimming',
                'Yoga',
                'Golf',
                'Gardening'
            ],
            'low' => [
                'Walking',
                'Stretching',
                'Tai Chi',
                'Light gardening',
                'Casual strolling'
            ]
        ];

        // Determine which intensity levels are safe based on AQI
        $recommendations = [];

        if ($aqi <= 50) {
            // Good air quality - all activities are fine
            $recommendations = [
                'high' => $activities['high'],
                'moderate' => $activities['moderate'],
                'low' => $activities['low']
            ];
        } elseif ($aqi <= 100) {
            // Moderate air quality - moderate and low intensity activities are recommended
            $recommendations = [
                'moderate' => $activities['moderate'],
                'low' => $activities['low']
            ];
        } elseif ($aqi <= 150) {
            // Unhealthy for sensitive groups - only low intensity activities
            $recommendations = [
                'low' => $activities['low']
            ];
        } else {
            // Unhealthy or worse - consider indoor activities
            $recommendations = [
                'indoor' => [
                    'Indoor yoga',
                    'Indoor gym workouts',
                    'Home exercises',
                    'Mall walking',
                    'Indoor swimming'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Get trend analysis for key pollutants
     */
    public function getTrends(Location $location, Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', Carbon::today()->subDays(6)->format('Y-m-d')));
        $endDate = Carbon::parse($request->input('end_date', Carbon::today()->format('Y-m-d')));

        $forecasts = $this->getForecasts($location, $startDate, $endDate);

        // Prepare trend data for each pollutant
        $trendData = [
            'dates' => $forecasts->pluck('forecast_date')->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            }),
            'aqi' => $forecasts->pluck('aqi'),
            'pm25' => $forecasts->pluck('pm25'),
            'pm10' => $forecasts->pluck('pm10'),
            'o3' => $forecasts->pluck('o3'),
            'no2' => $forecasts->pluck('no2'),
            'so2' => $forecasts->pluck('so2'),
            'co' => $forecasts->pluck('co'),
        ];

        return response()->json($trendData);
    }
}
