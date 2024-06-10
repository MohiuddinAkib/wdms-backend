<?php

namespace App\Domain\Wallet\Projections;

use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EventSourcing\Projections\Projection;

class Wallet extends Projection
{
    use HasFactory;

    protected $guarded = [];

    public function balance(): Attribute
    {
        // WE RETURN STRING TO KEEP THE PRECISION
        return Attribute::make(
            get: fn ($value) => (string) BigDecimal::of($value)->toScale(2, RoundingMode::DOWN)
        );
    }

    public function deposit(float|int|string $amount)
    {
        $result = BigDecimal::of($amount)
            ->plus(BigDecimal::of($this->balance))
            ->toScale(2, RoundingMode::DOWN);

        $this->writeable()->update([
            'balance' => $result,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, ownerKey: 'uuid');
    }

    public function denominations(): HasMany
    {
        return $this->hasMany(Denomination::class, 'wallet_id');
    }
}
