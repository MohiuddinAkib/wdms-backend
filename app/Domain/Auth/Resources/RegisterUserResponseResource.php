<?php

namespace App\Domain\Auth\Resources;

use Spatie\LaravelData\Resource;

class RegisterUserResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message
    ) {}
}
