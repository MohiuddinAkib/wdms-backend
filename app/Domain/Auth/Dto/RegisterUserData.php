<?php

namespace App\Domain\Auth\Dto;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Unique;

class RegisterUserData extends Data
{
    public function __construct(
      public string $name,
      #[Email()]
      #[Unique(table: 'users', column: 'email')]
      public string $email,
      #[Min(8)]
      public string $password
    ) {}
}
