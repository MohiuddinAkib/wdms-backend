<?php

namespace App\Domain\Currency\Resources;

use Spatie\LaravelData\Resource;

class CurrencyResource extends Resource
{
    public function __construct(
        public string $code,
        public ?string $name,
    ) {
    }
}
