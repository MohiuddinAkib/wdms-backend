<?php

namespace App\Domain\Currency\Resource;

use Spatie\LaravelData\Data;

class CurrencyData extends Data
{
    public function __construct(
        public string $code,
        public ?string $name,
    ) {
    }
}
