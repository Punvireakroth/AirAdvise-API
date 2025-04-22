<?php

namespace App\Services;

use App\Models\AirQualityForecast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ForecastAirQualityApiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.forecast_air_quality_api.key');
        $this->baseUrl = config('services.forecast_air_quality_api.url', 'http://api.openweathermap.org/data/2.5');
    }

    /**
     * Fetch forecast data for a location
     */
    public function getForecast($latitude, $longitude)
    {
        $cacheKey = "forecast:{$latitude}:{$longitude}";

        // Check cache first (3 hour cache - free tier refreshes data at this rate)
        return Cache::remember($cacheKey, 60 * 180, function () use ($latitude, $longitude) {
            try {
                // OpenWeatherMap Air Pollution API requires Unix timestamps
                $start = Carbon::now()->timestamp;
                $end = Carbon::now()->addDays(7)->timestamp;

                $response = Http::get("{$this->baseUrl}/air_pollution/forecast", [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    return $this->processApiResponse($response->json(), $latitude, $longitude);
                }

                Log::error("Air quality API error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error("Air quality API exception", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return null;
            }
        });
    }

    /**
     * Process OpenWeatherMap API response into our data structure
     */
    protected function processApiResponse($data, $latitude, $longitude)
    {
        $dailyForecasts = [];
        $forecastsByDay = [];

        // Group forecasts by day
        foreach ($data['list'] ?? [] as $item) {
            $date = Carbon::createFromTimestamp($item['dt'])->format('Y-m-d');

            if (!isset($forecastsByDay[$date])) {
                $forecastsByDay[$date] = [];
            }

            $forecastsByDay[$date][] = $item;
        }

        // Process each day to get daily averages
        foreach ($forecastsByDay as $date => $items) {
            $totalAqi = 0;
            $totalPm25 = 0;
            $totalPm10 = 0;
            $totalO3 = 0;
            $totalNo2 = 0;
            $totalSo2 = 0;
            $totalCo = 0;
            $count = count($items);

            foreach ($items as $item) {
                $components = $item['components'];
                $totalAqi += $item['main']['aqi']; // 1-5 scale in OpenWeatherMap
                $totalPm25 += $components['pm2_5'];
                $totalPm10 += $components['pm10'];
                $totalO3 += $components['o3'];
                $totalNo2 += $components['no2'];
                $totalSo2 += $components['so2'];
                $totalCo += $components['co'];
            }

            // Convert OpenWeatherMap AQI (1-5) to standard AQI
            $aqiValue = round($totalAqi / $count);
            $standardAqi = $this->convertToStandardAQI($aqiValue, $totalPm25 / $count);

            $forecast = [
                'forecast_date' => $date,
                'aqi' => $standardAqi,
                'pm25' => round($totalPm25 / $count, 2),
                'pm10' => round($totalPm10 / $count, 2),
                'o3' => round($totalO3 / $count, 2),
                'no2' => round($totalNo2 / $count, 2),
                'so2' => round($totalSo2 / $count, 2),
                'co' => round($totalCo / $count, 2),
                'category' => AirQualityForecast::getCategoryFromAQI($standardAqi),
            ];

            $forecast['description'] = AirQualityForecast::getDescriptionFromCategory($forecast['category']);
            $forecast['recommendation'] = AirQualityForecast::getRecommendationFromCategory($forecast['category']);

            $dailyForecasts[] = $forecast;
        }

        // Limit to 7 days
        return array_slice($dailyForecasts, 0, 7);
    }

    /**
     * Convert OpenWeatherMap AQI (1-5) to standard US AQI (0-500)
     */
    protected function convertToStandardAQI($owmAqi, $pm25)
    {
        // OpenWeatherMap uses 1-5 scale
        // 1: Good, 2: Fair, 3: Moderate, 4: Poor, 5: Very Poor

        // conversion based on PM2.5 value
        switch ($owmAqi) {
            case 1:
                return min(50, max(0, round($pm25 * 2))); // 0-50
            case 2:
                return min(100, max(51, round(50 + ($pm25 * 1.5)))); // 51-100
            case 3:
                return min(150, max(101, round(100 + ($pm25 * 0.8)))); // 101-150
            case 4:
                return min(200, max(151, round(150 + ($pm25 * 0.5)))); // 151-200
            case 5:
                return min(500, max(201, round(200 + ($pm25 * 0.8)))); // 201-500
            default:
                return 0;
        }
    }
}