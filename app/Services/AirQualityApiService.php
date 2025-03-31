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
                $endpoint = "{$this->baseUrl}/nearest_city";

                $response = Http::timeout(15)->get($endpoint, [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'key' => $this->apiKey,
                ]);

                $executionTime = round((microtime(true) - $startTime) * 1000);

                // Log the API request
                ApiRequestLog::create([
                    'api_name' => 'IQAir API',
                    'endpoint' => $endpoint,
                    'parameters' => json_encode([
                        'lat' => $latitude,
                        'lon' => $longitude,
                    ]),
                    'response_code' => $response->status(),
                    'execution_time' => $executionTime,
                    'created_at' => now(),
                ]);

                Log::info("Air Quality API request:", [
                    'url' => $endpoint,
                    'params' => ['lat' => $latitude, 'lon' => $longitude],
                    'status' => $response->status(),
                ]);

                if (!$response->successful()) {
                    Log::error("Air Quality API error: " . $response->body());
                    return null;
                }

                $data = $response->json();

                // DEBUG
                Log::info("Air Quality API successful response:", [
                    'data' => $data
                ]);

                return $this->formatAirQualityData($data);
            } catch (Exception $e) {
                Log::error("Air Quality API exception: " . $e->getMessage());
                return null;
            }
        });
    }

    protected function formatAirQualityData($data)
    {
        if (!isset($data['status']) || $data['status'] !== 'success' || !isset($data['data'])) {
            Log::error("Invalid air quality API response format:", ['data' => $data]);
            return null;
        }

        $pollution = $data['data']['current']['pollution'] ?? null;
        $weather = $data['data']['current']['weather'] ?? null;

        if (!$pollution) {
            Log::error("Missing pollution data in API response:", ['data' => $data]);
            return null;
        }

        $aqi = $pollution['aqius'] ?? 0;


        $category = $this->getAQICategory($aqi);

        return [
            'aqi' => $aqi,
            'pm25' => $pollution['aqius'] ?? 0, // IQAir primarily uses PM2.5 for US AQI
            'pm10' => $pollution['aqicn'] ?? 0, // Using China AQI as proxy for PM10
            'o3' => null, // IQAir free API doesn't provide individual pollutants
            'no2' => null,
            'so2' => null,
            'co' => null,
            'category' => $category,
            'source' => 'IQAir',
            'temperature' => $weather['tp'] ?? null,
            'humidity' => $weather['hu'] ?? null,
            'location_name' => $data['data']['city'] ?? 'Unknown',
            'country' => $data['data']['country'] ?? 'Unknown',
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