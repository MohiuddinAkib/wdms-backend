<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Domain\User\UserAggregate;
use Illuminate\Support\Facades\Hash;
use App\Domain\Auth\Dto\LoginUserData;
use App\Domain\Auth\Dto\RequestOtpData;
use App\Domain\Auth\Dto\RegisterUserData;
use App\Notifications\LoginOtpNotification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthController extends Controller
{
    public function register(RegisterUserData $data)
    {
        $userUuid = (string) Str::uuid();

        UserAggregate::retrieve($userUuid)
            ->register($data)
            ->persist();

        return response()->json([
            'success' => true,
            'message' => 'Registration successful'
        ], Response::HTTP_CREATED);
    }

    public function requestOtp(Request $request, RequestOtpData $data)
    {
        /** @var User|null */
        $user = User::whereEmail($data->email)->first();

        $validationError = ValidationException::withMessages([
            'email' => [trans('auth.failed')]
        ]);

        if(is_null($user)) {
           throw $validationError;
        }

        $passwordMatched = Hash::check($data->password, $user->getAuthPassword());

        if(!$passwordMatched) {
            throw $validationError;
        }

        $otp = otp()->make($user->email);

        $user->notify(new LoginOtpNotification($otp));

        

        if($request->wantsJson()) {
            return response([
                'success' => true,
                'message' => 'An otp has been sent to your mail.'
            ]);
        }

        return redirect()->route('/login');
    }

    public function login(Request $request, LoginUserData $data)
    {
        $matched = otp()->check($data->otp, $data->email);

        $validationError = ValidationException::withMessages([
            'otp' => ['Otp mismatched.']
        ]);

        if(!$matched) {
            throw $validationError;
        }

        /** @var User|null */
        $user = User::whereEmail($data->email)->first();
       
        if(is_null($user)) {
            throw $validationError;
        }

        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            auth()->login($user);

            if($request->hasSession()) {
                $request->session()->regenerate();
            }

            // FOR SPA NO NEED FOR ACCESS TOKEN
            if($request->wantsJson()) {
                return response([
                    'success' => true,
                    'message' => 'Login successful.',
                ]);
            }
            
            // IF NOT SPA BUT TRADITIONAL HTML SERVED BY LARAVEL
            return redirect()->intended('/');
        }

        // FOR OTHER API CLIENT NEED ACCESS TOKEN
        $token = $user->createToken('auth-token')->plainTextToken;

        return response([
            'success' => true,
            'token' => $token,
            'message' => 'Login successful.',
        ]);
    }
}
