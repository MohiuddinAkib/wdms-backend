<?php

namespace App\Domain\Currency\Resources;

use Spatie\LaravelData\Resource;

class DenominationResource extends Resource
{
    public function __construct(
      public string $name,
      public string $type,
      public float|int $value
    ) {}
}
