<?php

namespace App\Domain\User\Data\Resource;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
      public string $id,
      public string $name,
      public string $email,
    ) {}
}
