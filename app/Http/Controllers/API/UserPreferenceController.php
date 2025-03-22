<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Resources\UserPreferenceResource;
use App\Http\Requests\UserPreferenceRequest;

/**
 * @group User Preferences
 *
 * APIs for managing user preferences
 */
class UserPreferenceController extends Controller
{
    use ApiResponses;

    /**
     * Get user preferences
     * 
     * @authenticated
     * 
     * @response {
     *  "data": {
     *    "id": 1,
     *    "user_id": 1,
     *    "notification_enabled": true,
     *    "aqi_threshold": 100,
     *    "preferred_language": "en",
     *    "temperature_unit": "celsius",
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "message": null,
     *  "status": 200
     * }
     */
    public function show(Request $request)
    {
        $preferences = $request->user()->preference;

        if (!$preferences) {
            // Create default preferences if none exist
            $preferences = $request->user()->preference()->create([]);
        }

        return $this->success(new UserPreferenceResource($preferences));
    }

    /**
     * Update user preferences
     * 
     * @authenticated
     * 
     * @bodyParam notification_enabled boolean optional Enable/disable notifications. Example: true
     * @bodyParam aqi_threshold integer optional AQI threshold for notifications. Example: 100
     * @bodyParam preferred_language string optional Preferred language. Example: en
     * @bodyParam temperature_unit string optional Temperature unit (celsius or fahrenheit). Example: celsius
     * 
     * @response {
     *  "data": {
     *    "id": 1,
     *    "user_id": 1,
     *    "notification_enabled": true,
     *    "aqi_threshold": 150,
     *    "preferred_language": "en",
     *    "temperature_unit": "celsius",
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "message": "Preferences updated successfully",
     *  "status": 200
     * }
     */
    public function update(UserPreferenceRequest $request)
    {
        $user = $request->user();
        $preferences = $user->preference;

        if (!$preferences) {
            $preferences = $user->preference()->create($request->validated());
        } else {
            $preferences->update($request->validated());
        }

        return $this->success(
            new UserPreferenceResource($preferences),
            'Preferences updated successfully'
        );
    }
}
