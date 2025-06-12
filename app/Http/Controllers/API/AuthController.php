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
use Illuminate\Support\Facades\Auth;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Register a new user
     * 
     * @authenticated
     * 
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     * 
     * @response 201 {
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "role": "user",
     *    "email_verified_at": null,
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "token": "1|abcdefghijklmnopqrstuvwxyz"
     * }
     * 
     * @response 422 {
     *  "message": "The email has already been taken.",
     *  "errors": {
     *    "email": ["The email has already been taken."]
     *  }
     * }
     */
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

    /**
     * Login user
     * 
     * @authenticated
     * 
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password123
     * 
     * @response {
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "role": "user",
     *    "email_verified_at": "2023-01-01T00:00:00.000000Z",
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "token": "1|abcdefghijklmnopqrstuvwxyz"
     * }
     * 
     * @response 401 {
     *  "message": "Invalid credentials"
     * }
     */
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

    /**
     * Logout the authenticated user
     * 
     * @authenticated
     * 
     * @response {
     *  "data": null,
     *  "message": "Logged out successfully",
     *  "status": 200
     * }
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->success('Logged out successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to logout', 500);
        }
    }

    /**
     * Get authenticated user profile
     * 
     * @authenticated
     * 
     * @response {
     *  "id": 1,
     *  "name": "John Doe",
     *  "email": "john@example.com",
     *  "role": "user",
     *  "email_verified_at": "2023-01-01T00:00:00.000000Z",
     *  "created_at": "2023-01-01T00:00:00.000000Z",
     *  "updated_at": "2023-01-01T00:00:00.000000Z"
     * }
     */
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


    // Admin Auth
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin');
            }

            // Not an admin, logout and return with error
            Auth::logout();
            return back()->withErrors([
                'email' => 'You do not have admin privileges.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }


    public function adminLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
