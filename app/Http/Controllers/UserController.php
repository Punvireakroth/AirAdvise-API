<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use ApiResponses, AuthorizesRequests;

    public function show(Request $request)
    {
        return $this->success(new UserResource($request->user()));
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Only update if password is provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->success(new UserResource($user));
    }

    // Admin: List all users
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::paginate(10);

        return $this->success(
            UserResource::collection($users)->response()->getData(true)
        );
    }
}
