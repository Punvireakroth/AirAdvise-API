<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserLocationController extends Controller
{
    /**
     * Get user's saved locations
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $locations = $user->locations()
            ->with(['airQualityData' => function ($query) {
                $query->latest('timestamp');
            }])
            ->get()
            ->map(function ($location) {
                $currentAqi = $location->airQualityData->first();

                return [
                    'id' => $location->id,
                    'name' => $location->city_name,
                    'state' => $location->state_province,
                    'country' => $location->country,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'is_favorite' => (bool)$location->pivot->is_favorite,
                    'is_default' => (bool)$location->pivot->is_default,
                    'current_aqi' => $currentAqi ? $currentAqi->aqi : null,
                    'current_category' => $currentAqi ? $currentAqi->category : null,
                ];
            });

        return response()->json([
            'locations' => $locations
        ]);
    }

    /**
     * Save a location for the user
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $user = $request->user();
        $locationId = $request->input('location_id');

        // Check if user already has this location
        $existingLocation = $user->locations()
            ->wherePivot('location_id', $locationId)
            ->first();

        if ($existingLocation) {
            // Update to favorite if not already
            if (!$existingLocation->pivot->is_favorite) {
                $user->locations()->updateExistingPivot($locationId, [
                    'is_favorite' => true
                ]);

                return response()->json([
                    'message' => 'Location marked as favorite.'
                ]);
            }

            return response()->json([
                'message' => 'Location already saved.'
            ]);
        }

        // Set as default if this is the first location
        $isDefault = !$user->locations()->exists();

        // Attach new location
        $user->locations()->attach($locationId, [
            'is_favorite' => true,
            'is_default' => $isDefault
        ]);

        return response()->json([
            'message' => 'Location saved successfully.',
            'is_default' => $isDefault
        ], 201);
    }

    /**
     * Remove a location from user's saved locations
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Check if location exists in user's list
        $location = $user->locations()
            ->wherePivot('location_id', $id)
            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'Location not found in your saved locations.'
            ], 404);
        }

        // If this was the default location, we need to set a new default
        $wasDefault = $location->pivot->is_default;

        // Delete the relationship
        $user->locations()->detach($id);

        // If this was default and user has other locations, set a new default
        if ($wasDefault) {
            $newDefault = $user->locations()->first();
            if ($newDefault) {
                $user->locations()->updateExistingPivot($newDefault->id, [
                    'is_default' => true
                ]);
            }
        }

        return response()->json([
            'message' => 'Location removed successfully.'
        ]);
    }

    /**
     * Set a location as default
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        // Check if location exists in user's list
        $location = $user->locations()
            ->wherePivot('location_id', $id)
            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'Location not found in your saved locations.'
            ], 404);
        }

        // Begin transaction to ensure consistency
        DB::beginTransaction();

        try {
            // Remove default status from all user locations
            UserLocation::where('user_id', $user->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this location as default
            $user->locations()->updateExistingPivot($id, [
                'is_default' => true,
                'is_favorite' => true // Also mark as favorite if not already
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Default location set successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting default location: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to set default location. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a location
     */
    public function toggleFavorite(Request $request, $id)
    {
        $user = $request->user();

        // Check if location exists in user's list
        $location = $user->locations()
            ->wherePivot('location_id', $id)
            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'Location not found in your saved locations.'
            ], 404);
        }

        // Toggle favorite status
        $isFavorite = !$location->pivot->is_favorite;

        $user->locations()->updateExistingPivot($id, [
            'is_favorite' => $isFavorite
        ]);

        return response()->json([
            'message' => $isFavorite ? 'Location marked as favorite.' : 'Location removed from favorites.',
            'is_favorite' => $isFavorite
        ]);
    }
}
