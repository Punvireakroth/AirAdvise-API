<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirQualityDataResource;
use App\Models\AirQualityData;
use App\Models\Location;
use App\Models\Activity;
use App\Models\HealthTip;
use App\Services\AirQualityApiService;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Log;

class AirQualityController extends Controller
{
    use ApiResponses;

    protected $airQualityService;

    public function __construct(AirQualityApiService $airQualityService)
    {
        $this->airQualityService = $airQualityService;
    }

    public function getByLocation(Request $request, Location $location)
    {
        // Check if location belongs to user
        if (!$request->user()->locations()->where('locations.id', $location->id)->exists()) {
            return $this->error('Location not found', 404);
        }

        // Check if we have recent data (within last hour)
        $recentData = $location->airQualityData()
            ->where('timestamp', '>=', now()->subHour())
            ->latest('timestamp')
            ->first();

        if ($recentData) {
            // Get activity recommendations
            $safeActivities = Activity::where('max_safe_aqi', '>=', $recentData->aqi)->get();
            $unsafeActivities = Activity::where('max_safe_aqi', '<', $recentData->aqi)->get();

            // Get health tips
            $healthTips = HealthTip::where('min_aqi', '<=', $recentData->aqi)
                ->where('max_aqi', '>=', $recentData->aqi)
                ->get();

            return $this->success([
                'air_quality' => new AirQualityDataResource($recentData),
                'safe_activities' => $safeActivities,
                'unsafe_activities' => $unsafeActivities,
                'health_tips' => $healthTips,
            ]);
        }

        // If no recent data, fetch from API
        $airQualityData = $this->airQualityService->getAirQualityByCoordinates(
            $location->latitude,
            $location->longitude
        );

        if (!$airQualityData) {
            return $this->error('Unable to retrieve air quality data', 404);
        }

        // Store the data
        $recentData = AirQualityData::create([
            'location_id' => $location->id,
            'aqi' => $airQualityData['aqi'],
            'pm25' => $airQualityData['pm25'],
            'pm10' => $airQualityData['pm10'],
            'o3' => $airQualityData['o3'] ?? null,
            'no2' => $airQualityData['no2'] ?? null,
            'so2' => $airQualityData['so2'] ?? null,
            'co' => $airQualityData['co'] ?? null,
            'category' => $airQualityData['category'],
            'source' => $airQualityData['source'],
            'timestamp' => now(),
        ]);

        // Get activity recommendations
        $safeActivities = Activity::where('max_safe_aqi', '>=', $recentData->aqi)->get();
        $unsafeActivities = Activity::where('max_safe_aqi', '<', $recentData->aqi)->get();

        // Get health tips based on the last 1 hour air quality data
        $healthTips = HealthTip::where('min_aqi', '<=', $recentData->aqi)
            ->where('max_aqi', '>=', $recentData->aqi)
            ->get();

        return $this->success([
            'air_quality' => new AirQualityDataResource($recentData),
            'safe_activities' => $safeActivities,
            'unsafe_activities' => $unsafeActivities,
            'health_tips' => $healthTips,
        ]);
    }

    public function getCurrentByCoordinates(Request $request)
    {
        // Incomning request is a json object
        $requestData = $request->all();

        Log::info($requestData);

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Find or create location
        $location = Location::firstOrCreate(
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ],
            [
                'city_name' => 'Unknown Location',
                'country' => 'Unknown',
                'is_active' => true
            ]
        );

        // Check if we have recent data (within last hour)
        $recentData = $location->airQualityData()
            ->where('timestamp', '>=', now()->subHour())
            ->latest('timestamp')
            ->first();

        if (!$recentData) {
            try {
                // Fetch from external API
                $apiData = $this->airQualityService->getAirQualityByCoordinates(
                    $request->latitude,
                    $request->longitude
                );

                // Check if we got data back
                if (!$apiData) {
                    return $this->error('Unable to retrieve air quality data from external API', 503);
                }

                // Store the data
                $recentData = AirQualityData::create([
                    'location_id' => $location->id,
                    'aqi' => $apiData['aqi'] ?? 0,
                    'pm25' => $apiData['pm25'] ?? 0,
                    'pm10' => $apiData['pm10'] ?? 0,
                    'o3' => $apiData['o3'] ?? null,
                    'no2' => $apiData['no2'] ?? null,
                    'so2' => $apiData['so2'] ?? null,
                    'co' => $apiData['co'] ?? null,
                    'category' => $apiData['category'] ?? 'Good',
                    'source' => $apiData['source'] ?? 'IQAir',
                    'timestamp' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating air quality data: ' . $e->getMessage());
                return $this->error('Unable to retrieve air quality data: ' . $e->getMessage(), 503);
            }
        }

        // Get activity recommendations
        $safeActivities = Activity::where('max_safe_aqi', '>=', $recentData->aqi)->get();
        $unsafeActivities = Activity::where('max_safe_aqi', '<', $recentData->aqi)->get();

        // Get health tips
        $healthTips = HealthTip::where('min_aqi', '<=', $recentData->aqi)
            ->where('max_aqi', '>=', $recentData->aqi)
            ->get();

        return response()->json([
            'air_quality' => new AirQualityDataResource($recentData),
            'safe_activities' => $safeActivities,
            'unsafe_activities' => $unsafeActivities,
            'health_tips' => $healthTips,
        ]);
    }

    // Get historical air quality data for a location
    public function getHistorical(Request $request, Location $location)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        // Check if user has access to that location
        if (!$request->user()->locations()->where('locations.id', $location->id)->exists()) {
            return $this->error('Location not found', 404);
        }

        $days = $request->days ?? 7;

        $historicalData = $location->airQualityData()
            ->where('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->timestamp->format('Y-m-d');
            })
            ->map(function ($dayData) {
                // Get average AQI for each day
                return [
                    'date' => $dayData->first()->timestamp->format('Y-m-d'),
                    'aqi' => round($dayData->avg('aqi')),
                    'category' => $this->getAQICategoryFromAverage($dayData->avg('aqi')),
                    'readings_count' => $dayData->count()
                ];
            })
            ->values();

        return $this->success($historicalData);
    }

    // Helper method to get API category from average AQI
    protected function getAQICategoryFromAverage($averageAqi)
    {
        if ($averageAqi <= 50) {
            return 'Good';
        } elseif ($averageAqi <= 100) {
            return 'Moderate';
        } elseif ($averageAqi <= 150) {
            return 'Unhealthy for Sensitive Groups';
        } elseif ($averageAqi <= 200) {
            return 'Unhealthy';
        } elseif ($averageAqi <= 300) {
            return 'Very Unhealthy';
        } else {
            return 'Hazardous';
        }
    }
}