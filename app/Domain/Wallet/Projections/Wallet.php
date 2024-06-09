<?php

namespace App\Domain\Wallet\Projections;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Spatie\EventSourcing\Projections\Projection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Projection
{
    use HasFactory;

    protected $guarded = [];

    public function balance(): Attribute
    {
        // WE RETURN STRING TO KEEP THE PRECISION
        return Attribute::make(
            get: fn ($value) => (string) BigDecimal::ofUnscaledValue($value, $this->decimal_places)
        );
    }

    public function deposit(float|int|string $amount)
    {
        $decimalPlacesValue = $this->decimal_places;
        
        $decimalPlaces = BigDecimal::of(10)
            ->power($decimalPlacesValue)
            ->toScale(64, RoundingMode::DOWN);

        $result = BigDecimal::of($amount)
            ->multipliedBy(BigDecimal::of($decimalPlaces))
            ->toScale($decimalPlacesValue, RoundingMode::DOWN);

        $this->writeable()->increment('balance', $result);
    }
}
