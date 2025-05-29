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
use Illuminate\Support\Facades\Cache;
use App\Models\City;

class AirQualityController extends Controller
{
    use ApiResponses;

    protected $airQualityService;

    public function __construct(AirQualityApiService $airQualityService)
    {
        $this->airQualityService = $airQualityService;
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

    /**
     * Get air quality data for a specific location and zoom level
     */

    public function getMapData(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'zoom' => 'required|numeric|min:1|max:20',
            'pollutant' => 'required|string|in:AQI,NO2,PM25,PM10,O3,SO2,CO',
        ]);

        $latitude = $request->lat;
        $longitude = $request->lng;
        $zoom = $request->zoom;
        $pollutant = $request->pollutant;

        $cacheKey = "map_air_quality_{$latitude}_{$longitude}_{$zoom}_{$pollutant}";

        // Cache the result for 30 minutes
        return Cache::remember($cacheKey, 30 * 60, function () use ($latitude, $longitude, $zoom, $pollutant) {
            $pollutantData = $this->airQualityService->getMapTileData(
                $latitude,
                $longitude,
                $zoom,
                $pollutant
            );

            if (!$pollutantData) {
                return response()->json([
                    'error' => 'Could not retrieve air quality data',
                    'timestamp' => now()->toIso8601String(),
                ], 503);
            }

            $colorScheme = $this->getPollutantColorScheme($pollutant);
            $tileServerBaseUrl = config('services.air_quality.tile_server_url', 'https://tiles.airadvise.com/v1');
            $mapUrl = "{$tileServerBaseUrl}/{$pollutant}/{$colorScheme}";

            return response()->json([
                'mapUrl' => $mapUrl,
                'attribution' => 'Air quality data © OpenWeatherMap',
                'timestamp' => $pollutantData['timestamp'] ?? now()->toIso8601String(),
                'data' => $pollutantData,
            ]);
        });
    }

    private function getPollutantColorScheme($pollutant)
    {
        $schemes = [
            'AQI' => 'aqi_classic',
            'PM25' => 'pm25_gradient',
            'PM10' => 'pm10_gradient',
            'O3' => 'o3_scale',
            'NO2' => 'no2_scale',
            'SO2' => 'so2_scale',
            'CO' => 'co_scale',
        ];

        return $schemes[$pollutant] ?? 'aqi_classic';
    }

    /**
     * Get air quality data for a specific city.
     */
    public function getCityAirQuality($cityId)
    {
        $city = City::findOrFail($cityId);

        // Create a cache key based on city ID
        $cacheKey = "city_air_quality_{$cityId}";

        // Cache the result for 15 minutes
        return Cache::remember($cacheKey, 15 * 60, function () use ($city) {
            $airQualityData = $this->airQualityService->getCityAirQualityData($city);

            // Fallback data if API is down
            if (!$airQualityData) {
                $aqi = rand(20, 200);
                $pm25 = round(rand(5, 80) / 2, 1);
                $pm10 = round(rand(10, 120) / 2, 1);
                $o3 = round(rand(10, 100) / 2, 1);
                $no2 = round(rand(5, 80) / 2, 1);
                $so2 = round(rand(1, 40) / 2, 1);
                $co = round(rand(1, 30) / 10, 1);
                $category = $this->getAQICategoryFromAverage($aqi);

                Log::warning("Could not get real air quality data for city ID {$city->id}. Using fallback data.");

                return response()->json([
                    'id' => rand(10000, 99999),
                    'location_id' => $city->id,
                    'aqi' => $aqi,
                    'pm25' => $pm25,
                    'pm10' => $pm10,
                    'o3' => $o3,
                    'no2' => $no2,
                    'so2' => $so2,
                    'co' => $co,
                    'category' => $category,
                    'source' => 'AirAdvise API (generated)',
                    'timestamp' => now()->toIso8601String(),
                    'isLive' => false,
                    'pollutants' => "Generated by API - External API unavailable",
                ]);
            }

            // Calculate AQI category based on the real data
            $category = $this->getAQICategoryFromAverage($airQualityData['aqi']);

            return response()->json([
                'id' => rand(10000, 99999),
                'location_id' => $city->id,
                'aqi' => $airQualityData['aqi'],
                'pm25' => $airQualityData['pm25'],
                'pm10' => $airQualityData['pm10'],
                'o3' => $airQualityData['o3'],
                'no2' => $airQualityData['no2'],
                'so2' => $airQualityData['so2'],
                'co' => $airQualityData['co'],
                'category' => $category,
                'source' => 'OpenWeatherMap',
                'timestamp' => $airQualityData['timestamp'],
                'isLive' => true,
                'pollutants' => "Real data from OpenWeatherMap",
            ]);
        });
    }
}