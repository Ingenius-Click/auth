<?php

namespace Ingenius\Auth\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Ingenius\Auth\Http\Resources\UserResource;
use Ingenius\Auth\Models\User;
use Ingenius\Auth\Rules\PasswordRegex;
use Ingenius\Core\Helpers\AuthHelper;
use Ingenius\Core\Interfaces\HasCustomerProfile;

class TenantAuthController extends Controller
{
    /**
     * Register a new tenant user
     */
    public function register(Request $request)
    {
        $userClass = tenant_user_class();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                new PasswordRegex()
            ],
        ], [
            'password.min' => __('auth::validation.password.min'),
        ]);


        $user = $userClass::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Trigger the Registered event (Laravel will automatically send verification email if MustVerifyEmail is implemented)
        event(new Registered($user));

        // If API request, return response (no token until email is verified)
        if ($request->wantsJson()) {
            return Response::api(
                message: 'Registration successful! Please check your email to verify your account before logging in.',
                data: [
                    'email' => $user->email,
                    'email_verified' => $user->hasVerifiedEmail(),
                ],
                code: 201
            );
        }

        // If web request, redirect to verification notice
        return redirect()->route('verification.notice');
    }

    /**
     * Login user and create token or session
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (!Auth::guard('tenant')->attempt($request->only('email', 'password'))) {
            if ($request->wantsJson()) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
        }

        $userClass = tenant_user_class();
        $user = $userClass::where('email', $request->email)->first();

        // Check if email verification is required
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            // Logout the user
            Auth::guard('tenant')->logout();

            // Resend verification email
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }

            if ($request->wantsJson()) {
                return Response::api(
                    message: 'Your email address is not verified. A new verification link has been sent to your email.',
                    data: ['email_verified' => false],
                    code: 403
                );
            }

            return back()->withErrors([
                'email' => 'Please verify your email address before logging in. A new verification link has been sent to your email.',
            ])->withInput($request->except('password'));
        }

        // If API request, return token
        if ($request->wantsJson()) {
            // Revoke previous tokens if requested
            if ($request->input('revoke_tokens', false)) {
                $user->tokens()->delete();
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return Response::api(message: 'Login successful', data: [
                'user' => $user,
                'token' => $token,
            ]);
        }

        // If web request, continue with session auth and redirect
        return redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * Logout user (revoke token or clear session)
     */
    public function logout(Request $request)
    {
        // For session authentication
        if (Auth::guard('tenant')->check()) {
            Auth::guard('tenant')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // For token authentication
        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                // Revoke the current token
                $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully',
            ]);
        }

        return redirect()->route('tenant.login');
    }

    /**
     * Get the authenticated user
     */
    public function user(Request $request)
    {
        $user = AuthHelper::getUser();

        return Response::api(
            data: [
                'user' => new UserResource(
            $user
            )
            ],
            message: 'User retrieved successfully'
        );
    }
}
