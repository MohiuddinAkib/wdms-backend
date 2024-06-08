<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Domain\User\UserAggregate;
use App\Domain\Auth\Dto\RegisterUserData;
use Symfony\Component\HttpFoundation\Response;

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
}
