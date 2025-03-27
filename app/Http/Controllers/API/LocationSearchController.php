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

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:100',
        ]);

        // First, search in the db
        $dbLocations = Location::where('city_name', 'like', '%' . $request->query . '%')
            ->orWhere('country', 'like', '%' . $request->query . '%')
            ->limit(5)
            ->get();

        // If result found, return them
        if ($dbLocations->count() >= 5) {
            return $this->success(LocationResource::collection($dbLocations));
        }

        // If no results found, use geocoding API
        $apiLocations = $this->geocodingService->searchLocations($request->query);


        // Save new locations to db avoid new future requests to the API
        foreach ($apiLocations as $location) {
            if (!$location->exists) {
                $location->save();
            }
        }


        // Merge db and api result and return
        $mergedLocations = $dbLocations->merge($apiLocations)->unique('id');

        return $this->success(LocationResource::collection($mergedLocations));
    }


    public function reserveGeocoding(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $location = $this->geocodingService->reverseGeocode(
            $request->latitude,
            $request->longitude
        );

        if (!$location) {
            return $this->error('Location not found', 404);
        }

        // Save location to database if it's new
        if (!$location->exists) {
            $location->save();
        }

        return $this->success(new LocationResource($location));
    }
}
