<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use App\Models\Location;
use App\Http\Resources\LocationResource;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $locations = $request->user()->locations()
            ->when($request->has('is_favorite'), function ($query) use ($request) {
                return $query->wherePivot('is_favorite', $request->boolean('is_favorite'));
            })
            ->paginate(10);

        return $this->success($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_name' => 'required|string|max:255',
            'state_province' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_favorite' => 'sometimes|boolean',
        ]);

        // Check if location is already exists
        $location = Location::where([
            'city_name' => $validated['city_name'],
            'country' => $validated['country'],
        ])->first();

        // If not, create new location
        if (!$location) {
            $location = Location::create([
                'city_name' => $validated['city_name'],
                'country' => $validated['country'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'is_active' => true,
            ]);
        }

        // Associate location with user
        $request->user()->locations()->syncWithoutDetaching([
            $location->id => ['is_favorite' => $validated['is_favorite'] ?? false]
        ]);

        return $this->success(new LocationResource($location), 'Location added successfully', 201);
    }

    public function show(Request $request, Location $location)
    {
        // Check if user has access to this location
        $location = $request->user()->locations()->where('locations.id', $location->id)->exists();

        if (!$location) {
            return $this->error('Location not found', 404);
        }

        return $this->success(new LocationResource($location));
    }

    public function destroy(Request $request, Location $location)
    {
        // Remove location from user's locations
        $request->user()->locations()->detach($location->id);

        return $this->success(null, 'Location removed successfully');
    }


    public function toggleFavorite(Request $request, Location $location)
    {
        $userLocation = $request->user()->locations()->where('locations.id', $location->id)->first();

        if (!$userLocation) {
            return $this->error('Location not found', 404);
        }

        // Get current favorite status and toggle it
        $isFav = !$request->user()->locations()
            ->where('locations.id', $location->id)
            ->first()
            ->pivot
            ->is_favorite;

        // Update favorite status
        $request->user()->locations()->updateExistingPivot($location->id, ['is_favorite' => !$isFav]);

        // After toggle favorite log
        Log::info('User ' . $request->user()->id . ' toggled favorite status for location ' . $location->id . ' to ' . $isFav);

        return $this->success([
            'is_favorite' => $isFav,
            'message' => 'Favorite status updated successfully'
        ]);
    }
}
