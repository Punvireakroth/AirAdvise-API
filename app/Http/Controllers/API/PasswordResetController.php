<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @group Password Reset
 *
 * APIs for password reset functionality
 */
class PasswordResetController extends Controller
{
    use ApiResponses;

    /**
     * Send password reset link
     * 
     * @authenticated
     * 
     * @bodyParam email string required The email address. Example: john@example.com
     * 
     * @response {
     *  "data": null,
     *  "message": "Password reset link sent to your email",
     *  "status": 200
     * }
     * 
     * @response 400 {
     *  "message": "Unable to send reset link",
     *  "status": 400
     * }
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, 'Reset link sent to email');
        } else {
            return $this->error('Unable to send reset link', 400);
        }
    }

    /**
     * Reset password
     * 
     * @authenticated
     * 
     * @bodyParam token string required The password reset token. Example: abcdef123456
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The new password. Example: newpassword123
     * @bodyParam password_confirmation string required Password confirmation. Example: newpassword123
     * 
     * @response {
     *  "data": null,
     *  "message": "Password reset successfully",
     *  "status": 200
     * }
     * 
     * @response 400 {
     *  "message": "Unable to reset password",
     *  "status": 400
     * }
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Password reset successfully');
        } else {
            return $this->error('Unable to reset password', 400);
        }
    }
}
