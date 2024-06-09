<?php

namespace App\Domain\Auth\Dto;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class RequestOtpData extends Data
{
    public function __construct(
        #[Email()]
        public string $email,
        #[Min(8)]
        public string $password
    ) {
    }
}
