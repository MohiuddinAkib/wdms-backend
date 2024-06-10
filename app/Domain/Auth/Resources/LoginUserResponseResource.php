<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Spatie\LaravelData\Resource;
use Symfony\Component\HttpFoundation\Response;

class LoginUserResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message,
      public ?string $token
    ) {}

    protected function calculateResponseStatus(Request $request): int
    {
      return Response::HTTP_OK;
    }
}
