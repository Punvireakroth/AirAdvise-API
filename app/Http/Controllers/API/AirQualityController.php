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

class AirQualityController extends Controller
{
    protected $airQualityService;

    public function __construct(AirQualityApiService $airQualityService)
    {
        $this->airQualityService = $airQualityService;
    }

    public function getCurrentByCoordinates(Request $request)
    {
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
            ['name' => 'Unnamed Location']
        );

        // Check if we have recent data (within last hour)
        $recentData = $location->airQualityData()
            ->where('timestamp', '>=', now()->subHour())
            ->latest('timestamp')
            ->first();

        if (!$recentData) {
            // Fetch from external API
            $apiData = $this->airQualityService->getAirQualityByCoordinates(
                $request->latitude,
                $request->longitude
            );

            // Store the data
            $recentData = AirQualityData::create([
                'location_id' => $location->id,
                'aqi' => $apiData['aqi'],
                'pm25' => $apiData['pm25'],
                'pm10' => $apiData['pm10'],
                'o3' => $apiData['o3'] ?? null,
                'no2' => $apiData['no2'] ?? null,
                'so2' => $apiData['so2'] ?? null,
                'co' => $apiData['co'] ?? null,
                'category' => $apiData['category'],
                'source' => $apiData['source'],
                'timestamp' => now(),
            ]);
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
}
