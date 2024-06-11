<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelData\Resource;

class UserLogoutResponseResource extends Resource
{
    public function __construct(
        public bool $success,
        public string $message
    ) {
    }

    protected function calculateResponseStatus(Request $request): int
    {
        return Response::HTTP_OK;
    }
}
