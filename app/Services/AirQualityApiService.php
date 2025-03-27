<?php

namespace App\Services;

use App\Models\ApiRequestLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AirQualityApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.air_quality_api.url');
        $this->apiKey = config('services.air_quality_api.key');
    }

    public function getAirQualityByCoordinates($latitude, $longitude)
    {
        $cacheKey = "air_quality_{$latitude}_{$longitude}";

        // Cache for 1 hour since air quality data doesn't change very often
        return Cache::remember($cacheKey, now()->addHour(), function () use ($latitude, $longitude) {
            $startTime = microtime(true);

            try {
                $response = Http::timeout(15)->get(
                    $this->baseUrl,
                    [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'key' => $this->apiKey,
                    ]
                );

                $executionTime = round((microtime(true) - $startTime) * 1000);

                // Log the API request
                ApiRequestLog::create([
                    'api_name' => 'AirQuality API',
                    'endpoint' => $this->baseUrl,
                    'parameters' => json_encode([
                        'lat' => $latitude,
                        'lon' => $longitude,
                    ]),
                    'response_code' => $response->status(),
                    'execution_time' => $executionTime,
                ]);

                if (!$response->successful()) {
                    Log::error("Air Quality API error: " . $response->body());
                    return null;
                }

                $data = $response->json();
                return $this->formatAirQualityData($data);
            } catch (Exception $e) {
                Log::error("Air Quality API exception: " . $e->getMessage());
                return null;
            }
        });
    }

    protected function formatAirQualityData($data)
    {
        $aqi = $data['data']['aqi'] ?? 0;

        return [
            'aqi' => $aqi,
            'pm25' => $data['data']['iaqi']['pm25']['v'] ?? 0,
            'pm10' => $data['data']['iaqi']['pm10']['v'] ?? 0,
            'o3' => $data['data']['iaqi']['o3']['v'] ?? null,
            'no2' => $data['data']['iaqi']['no2']['v'] ?? null,
            'so2' => $data['data']['iaqi']['so2']['v'] ?? null,
            'co' => $data['data']['iaqi']['co']['v'] ?? null,
            'category' => $this->getAQICategory($aqi),
            'source' => $data['data']['attributions'][0]['name'] ?? 'Unknown',
        ];
    }

    protected function getAQICategory($aqi)
    {
        if ($aqi <= 50) {
            return 'Good';
        } elseif ($aqi <= 100) {
            return 'Moderate';
        } elseif ($aqi <= 150) {
            return 'Unhealthy for Sensitive Groups';
        } elseif ($aqi <= 200) {
            return 'Unhealthy';
        } elseif ($aqi <= 300) {
            return 'Very Unhealthy';
        } else {
            return 'Hazardous';
        }
    }
}
