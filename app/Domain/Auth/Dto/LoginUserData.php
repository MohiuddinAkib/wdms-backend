<?php

namespace App\Domain\Auth\Dto;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

class LoginUserData extends Data
{
    public function __construct(
        #[Email()]
        public string $email,
        public string $otp
    ) {
    }
}
