<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'source' => 'sometimes|string|in:store,backoffice',
        ]);

        $source = $request->input('source', 'store');
        $userClass = tenant_user_class();

        // Find the user to pass source to the notification
        $user = $userClass::where('email', $request->email)->first();

        if ($user) {
            // Generate token manually so we can control the notification
            $token = Password::broker('tenant_users')->createToken($user);

            // Send notification with source parameter
            $user->sendPasswordResetNotification($token, $source);
        }

        // For security reasons, we always return a generic success message
        // This prevents email enumeration attacks
        return Response::api(
            data: null,
            message: __('auth::passwords.sent'),
        );
    }
}
