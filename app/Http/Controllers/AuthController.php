<?php

namespace App\Http\Controllers;

use App\Domain\Auth\Dto\LoginUserData;
use App\Domain\Auth\Dto\RegisterUserData;
use App\Domain\Auth\Dto\RequestOtpData;
use App\Domain\Auth\Resources\LoginUserResponseResource;
use App\Domain\Auth\Resources\RequestOtpResponseResource;
use App\Domain\Auth\Resources\RegisterUserResponseResource;
use App\Domain\User\UserAggregate;
use App\Models\User;
use App\Notifications\LoginOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthController extends Controller
{
    public function register(RegisterUserData $data): RegisterUserResponseResource
    {
        $userUuid = (string) Str::uuid();

        UserAggregate::retrieve($userUuid)
            ->register($data)
            ->persist();

        return new RegisterUserResponseResource(
            success: true,
            message: 'Registration successful'
        );
    }

    public function requestOtp(Request $request, RequestOtpData $data): RequestOtpResponseResource|RedirectResponse
    {
        /** @var User|null */
        $user = User::whereEmail($data->email)->first();

        $validationError = ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);

        if (is_null($user)) {
            throw $validationError;
        }

        $passwordMatched = Hash::check($data->password, $user->getAuthPassword());

        if (! $passwordMatched) {
            throw $validationError;
        }

        $otp = otp()->make($user->email);

        $user->notify(new LoginOtpNotification($otp));

        if ($request->wantsJson()) {
            return new RequestOtpResponseResource(
                success: true,
                message: 'An otp has been sent to your mail.',
            );
        }

        return redirect()->route('/login');
    }

    public function login(Request $request, LoginUserData $data): LoginUserResponseResource|RedirectResponse
    {
        $matched = otp()->check($data->otp, $data->email);

        $validationError = ValidationException::withMessages([
            'otp' => ['Otp mismatched.'],
        ]);

        if (! $matched) {
            throw $validationError;
        }

        /** @var User|null */
        $user = User::whereEmail($data->email)->first();

        if (is_null($user)) {
            throw $validationError;
        }

        if (!EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            // FOR NON STATEFUL API CLIENT NEED ACCESS TOKEN
            $token = $user->createToken('auth-token')->plainTextToken;

            return new LoginUserResponseResource(
                success: true,
                message: 'Login successful.',
                token: $token
            );
        }

        // FOR STATEFUL API CLIENT USE SESSION BASED AUTHENTICATION
        auth()->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // FOR SPA NO NEED FOR ACCESS TOKEN
        if ($request->wantsJson()) {
            return new LoginUserResponseResource(
                success: true,
                message: 'Login successful.',
                token: null
            );
        }

        // IF NOT SPA BUT TRADITIONAL HTML SERVED BY LARAVEL
        return redirect()->intended('/');
    }
}
