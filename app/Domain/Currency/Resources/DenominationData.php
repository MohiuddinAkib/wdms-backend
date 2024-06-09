<?php

namespace App\Domain\Currency\Resources;

use Spatie\LaravelData\Data;

class DenominationData extends Data
{
    public function __construct(
      public string $name,
      public string $type,
      public float|int $value
    ) {}
}
