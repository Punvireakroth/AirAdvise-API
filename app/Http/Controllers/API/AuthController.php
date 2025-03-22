<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\UserPreference;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    use ApiResponses;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create default preferences
        UserPreference::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    // Log the user out 
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->success('Logged out successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to logout', 500);
        }
    }

    // Return authenticated user profile
    public function profile(Request $request)
    {
        // Current authenticated user
        try {
            $authUser = $request->user();
            return new UserResource($authUser);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch user profile', 500);
        }
    }

    // Update authenticated user profile
    public function update(Request $request)
    {
        // Current authenticated user
        try {
            $authUser = $request->user();
            $authUser->update($request->all());
            return new UserResource($authUser);
        } catch (\Exception $e) {
            return $this->error('Failed to update user profile', 500);
        }
    }
}
