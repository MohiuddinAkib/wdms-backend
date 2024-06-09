<?php

namespace App\Domain\Auth\Resources;

use Spatie\LaravelData\Resource;

class LoginUserResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message,
      public ?string $token
    ) {}
}
