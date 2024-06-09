<?php

namespace App\Domain\Auth\Resources;

use Spatie\LaravelData\Resource;

class RequestOtpResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message
    ) {}
}
