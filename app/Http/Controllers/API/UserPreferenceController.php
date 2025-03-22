<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Resources\UserPreferenceResource;
use App\Http\Requests\UserPreferenceRequest;

class UserPreferenceController extends Controller
{
    use ApiResponses;

    public function show(Request $request)
    {
        $preferences = $request->user()->preference;

        if (!$preferences) {
            // Create default preferences if none exist
            $preferences = $request->user()->preference()->create([]);
        }

        return $this->success(new UserPreferenceResource($preferences));
    }


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
