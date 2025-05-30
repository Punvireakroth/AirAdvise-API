<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserFavoriteCity;
use App\Models\City;

class UserLocationController extends Controller
{
    /**
     * Get all user's favorite cities
     */

    public function index(Request $request)
    {
        $user = $request->user();
        $favoriteCities = $user->favoriteCities()
            ->orderBy('name')
            ->get();

        return response()->json($favoriteCities);
    }

    /**
     * Add a new favorite city
     */

    public function store(Request $request)
    {
        $request->validate([
            'cityId' => 'required|exists:cities,id',
        ]);

        $user = $request->user();
        $cityId = $request->input('cityId');

        // Check if city is already favorited
        $existingFavorite = UserFavoriteCity::where('user_id', $user->id)
            ->where('city_id', $cityId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'City is already in favorites',
            ], 409); // Conflict
        }

        // If first fav make it default 
        $isDefault = UserFavoriteCity::where('user_id', $user->id)->count() === 0;

        $user->favoriteCities()->attach($cityId, [
            'is_default' => $isDefault,
        ]);

        $city = City::find($cityId);

        return response()->json([
            'message' => 'City added to favorites',
            'city' => $city,
            'is_default' => $isDefault,
        ], 201);
    }

    /**
     * Remove a favorite city
     */

    public function destroy(Request $request, $cityId)
    {
        $user = $request->user();

        $wasDefault = UserFavoriteCity::where('user_id', $user->id)
            ->where('city_id', $cityId)
            ->where('is_default', true)
            ->exists();

        $removed = $user->favoriteCities()->detach($cityId);

        if ($removed === 0) {
            return response()->json([
                'message' => 'City was not in favorites',
            ], 404);
        }

        // If remove default set new default
        if ($wasDefault) {
            $firstFavorite = $user->favoriteCities()->first();

            if ($firstFavorite) {
                UserFavoriteCity::where('user_id', $user->id)
                    ->where('city_id', $firstFavorite->id)
                    ->update(['is_default' => true]);
            }
        }

        return response()->json([
            'message' => 'City removed from favorites',
        ]);
    }

    /**
     * Set default city
     */

    public function setDefault(Request $request, $cityId)
    {
        $request->validate([
            'cityId' => 'required|exists:cities,id',
        ]);

        $user = $request->user();
        $cityId = $request->cityId;

        // Check if city is in favorites
        $isFavorite = UserFavoriteCity::where('user_id', $user->id)
            ->where('city_id', $cityId)
            ->exists();

        if (!$isFavorite) {
            return response()->json([
                'message' => 'City is not in favorites',
            ], 404);
        }

        UserFavoriteCity::where('user_id', $user->id)
            ->update(['is_default' => false]);

        // Set new default
        UserFavoriteCity::where('user_id', $user->id)
            ->where('city_id', $cityId)
            ->update(['is_default' => true]);

        return response()->json([
            'message' => 'Default city updated',
        ]);
    }


    /**
     * For Admin return analytics data
     */

    public function getAnalytics(Request $request) {}
}
