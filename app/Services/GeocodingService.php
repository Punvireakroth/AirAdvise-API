<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class GeocodingService
{

    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.geocoding.key');
        $this->baseUrl = config('services.geocoding.url');
    }

    public function searchLocations($query)
    {
        $cacheKey = "geocode_search_" . md5($query);


        return Cache::remember($cacheKey, now()->addDay(), function () use ($query) {
            $startTime = microtime(true);

            $response = Http::get($this->baseUrl, [
                'key' => $this->apiKey,
                'q' => $query,
                'format' => 'json',
                'limit' => 5,
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Log the API request
            Log::create([
                'api_name' => 'Geocoding API',
                'endpoint' => $this->baseUrl,
                'parameters' => json_encode(['q' => $query]),
                'response_code' => $response->status(),
                'execution_time' => $executionTime,
            ]);


            $results = $response->json('results', []);

            return collect($results)->map(function ($item) {
                // Create location models without saving them
                return new Location([
                    'city_name' => $item['components']['city'] ?? $item['components']['town'] ?? $item['components']['village'] ?? $item['name'],
                    'state_province' => $item['components']['state'] ?? null,
                    'country' => $item['components']['country'] ?? '',
                    'country_code' => $item['components']['country_code'] ?? null,
                    'latitude' => $item['geometry']['lat'] ?? null,
                    'longitude' => $item['geometry']['lng'] ?? null,
                    'timezone' => null,
                    'is_active' => true,
                ]);
            });
        });
    }


    public function reverseGeocode(float $latitude, float $longitude)
    {
        $cacheKey = "reverse_geocode_{$latitude}_{$longitude}";


        return Cache::remember($cacheKey, now()->addDay(), function () use ($latitude, $longitude) {
            $startTime = microtime(true);

            $response = Http::get($this->baseUrl, [
                'key' => $this->apiKey,
                'q' => "{$latitude},{$longitude}",
                'format' => 'json',
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Log the API request
            Log::create([
                'api_name' => 'Reverse Geocoding API',
                'endpoint' => $this->baseUrl,
                'parameters' => json_encode(['lat' => $latitude, 'lon' => $longitude]),
                'response_code' => $response->status(),
                'execution_time' => $executionTime,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $results = $response->json('results', []);

            if (empty($results)) {
                return null;
            }

            $item = $results[0];

            return new Location([
                'city_name' => $item['components']['city'] ?? $item['components']['town'] ?? $item['components']['village'] ?? 'Unknown',
                'state_province' => $item['components']['state'] ?? null,
                'country' => $item['components']['country'] ?? 'Unknown',
                'country_code' => $item['components']['country_code'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => null,
            ]);
        });
    }
}
