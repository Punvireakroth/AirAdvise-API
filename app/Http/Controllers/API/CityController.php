<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Http\Controllers\Controller;


class CityController extends Controller
{
    use ApiResponses;

    /**
     * Search for cities based on query
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return $this->error('Query is required', 400);
        }

        // "nearby" search
        if (strpos($query, 'nearby:') === 0) {
            // Extract coordinates from query: nearby:lat,lng
            $coordinates = substr($query, strlen('nearby:'));
            list($latitude, $longitude) = explode(',', $coordinates);

            // Validate coordinates
            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                return $this->error('Invalid coordinates format', 400);
            }

            // Cache key for this location search
            $cacheKey = "cities_near_{$latitude}_{$longitude}";

            return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
                // Find cities near these coordinates using the Haversine formula
                $cities = DB::select(
                    'SELECT *,
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude)))) AS distance
                    FROM cities
                    HAVING distance < 50
                    ORDER BY distance
                    LIMIT 10',
                    [$latitude, $longitude, $latitude]
                );

                return $this->success($cities);
            });
        }

        // Regular name-based search
        // Cache search results for 1 hour
        $cacheKey = "city_search_" . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            // Perform fuzzy search on city name
            $cities = City::where('name', 'like', "%{$query}%")
                ->orWhere('country', 'like', "%{$query}%")
                ->orderBy('name')
                ->paginate(20);

            return response()->json($cities);
        });
    }

    /**
     * Get details for a specific city
     */
    public function show($id)
    {
        $city = City::find($id);

        if (!$city) {
            return $this->error('City not found', 404);
        }

        return $this->success($city);
    }

    /**
     * Get a list of cities from A to Z
     * 
     */
    public function index(Request $request)
    {
        $letter = $request->query('letter');
        $country = $request->query('country');

        $query = City::query();

        if ($letter) {
            $query->where('name', 'like', $letter . '%');
        }

        if ($country) {
            $query->where('country', $country);
        }

        $cities = $query->orderBy('name')->paginate(50);

        // Group cities by first letter for alphabetical indexing
        $indexedCities = $cities->groupBy(function ($city) {
            return strtoupper(substr($city->name, 0, 1));
        });

        return response()->json([
            'cities' => $cities,
            'indexed' => $indexedCities,
        ]);
    }
}