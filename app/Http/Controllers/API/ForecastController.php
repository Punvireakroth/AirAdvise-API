<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AirQualityForecast;
use App\Models\Location;
use App\Services\ForecastAirQualityApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ForecastController extends Controller
{
    protected $airQualityService;

    public function __construct(ForecastAirQualityApiService $airQualityService)
    {
        $this->airQualityService = $airQualityService;
    }

    /**
     * Get forecasts for a specific location
     */
    public function getByLocation(Location $location, Request $request)
    {
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

        // Calculate best day if activities filter provided
        $bestDay = null;
        if ($request->has('activity_type')) {
            $bestDay = $this->calculateBestDay($forecasts, $request->input('activity_type'));
        }

        return response()->json([
            'forecasts' => $forecasts,
            'best_day' => $bestDay,
        ]);
    }

    /**
     * Get forecasts by coordinates
     */
    public function getByCoordinates(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Find or create location
        $location = Location::firstOrCreate(
            ['latitude' => $latitude, 'longitude' => $longitude],
            [
                'name' => "Location at {$latitude}, {$longitude}",
                'country' => 'Unknown', // Should be resolved via reverse geocoding
            ]
        );

        return $this->getByLocation($location, $request);
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
        $externalForecasts = $this->airQualityService->getForecast($location->latitude, $location->longitude);

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

        // If external API fails, return whatever we have
        return $existingForecasts;
    }

    /**
     * Calculate best day for a given activity type
     */
    protected function calculateBestDay($forecasts, $activityType)
    {
        if ($forecasts->isEmpty()) {
            return null;
        }

        // Define AQI thresholds for different activity types
        $thresholds = [
            'low' => 100,      // Low intensity (walking, yoga)
            'moderate' => 75,  // Moderate intensity (hiking, cycling)
            'high' => 50       // High intensity (running, sports)
        ];

        $activityType = strtolower($activityType);
        $threshold = $thresholds[$activityType] ?? 75; // Default to moderate

        // Filter days below threshold
        $suitableDays = $forecasts->filter(function ($forecast) use ($threshold) {
            return $forecast->aqi <= $threshold;
        });

        // If no days are suitable, return the one with lowest AQI
        if ($suitableDays->isEmpty()) {
            return $forecasts->sortBy('aqi')->first();
        }

        // Otherwise return the best suitable day
        return $suitableDays->sortBy('aqi')->first();
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