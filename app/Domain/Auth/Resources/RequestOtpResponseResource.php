<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Spatie\LaravelData\Resource;
use Symfony\Component\HttpFoundation\Response;

class RequestOtpResponseResource extends Resource
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
