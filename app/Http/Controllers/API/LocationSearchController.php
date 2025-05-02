<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GeocodingService;
use App\Http\Resources\LocationResource;

class LocationSearchController extends Controller
{
    use ApiResponses;

    protected $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Search for a location by query
     */

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        $query = $request->query;
        $results = $this->geocodingService->searchLocations($query);
        $user = $request->user();

        if (empty($results)) {
            return $this->error('No locations found', 404);
        }

        $locations = [];

        foreach ($results as $result) {
            // Skip entries if query is without these
            if (empty($result['city']) || empty($result['latitude']) || empty($result['longitude'])) {
                continue;
            }

            // Check if location already exists in db
            $location = Location::firstOrCreate(
                [
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude']
                ],
                [
                    'city_name' => $result['city'],
                    'state_province' => $result['state'],
                    'country' => $result['country'],
                    'country_code' => $result['country_code'],
                    'timezone' => $result['timezone'],
                    'is_active' => true
                ]
            );

            // Check if it's a favorite location for current user
            $isFavorite = false;
            if ($user) {
                $isFavorite = $user->locations()
                    ->wherePivot('location_id', $location->id)
                    ->wherePivot('is_favorite', true)
                    ->exists();
            }

            $locations[] = [
                'id' => $location->id,
                'name' => $location->city_name,
                'state' => $location->state_province,
                'country' => $location->country,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'is_favorite' => $isFavorite
            ];
        }

        return $this->success($locations);
    }


    /**
     * Reverse geocoding to get location details by coordinates
     */
    public function reserveGeocoding(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $locationDetails = $this->geocodingService->reverseGeocode($latitude, $longitude);

        if (empty($locationDetails)) {
            return $this->error('Location not found', 404);
        }

        $location = Location::firstOrCreate(
            [
                'latitude' => $locationDetails['latitude'],
                'longitude' => $locationDetails['longitude']
            ],
            [
                'city_name' => $locationDetails['city'],
                'state_province' => $locationDetails['state'],
                'country' => $locationDetails['country'],
                'country_code' => $locationDetails['country_code'],
                'timezone' => $locationDetails['timezone'],
                'is_active' => true
            ]
        );

        // Check if it's a favorite location for current user
        $isFavorite = false;
        if ($user) {
            $isFavorite = $user->locations()
                ->wherePivot('location_id', $location->id)
                ->wherePivot('is_favorite', true)
                ->exists();
        }

        $locationData = [
            'id' => $location->id,
            'name' => $location->city_name,
            'state' => $location->state_province,
            'country' => $location->country,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'is_favorite' => $isFavorite
        ];

        return $this->success($locationData);
    }
}