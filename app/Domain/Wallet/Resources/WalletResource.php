<?php

namespace App\Domain\Wallet\Resources;

use App\Domain\Wallet\Projections\Wallet;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapOutputName(SnakeCaseMapper::class)]
class WalletResource extends Resource
{
    /** @param array<int, WalletDenominationResource> $denominations */
    public function __construct(
        public string $id,
        public string $currency,
        public string $balance,
        public array $denominations
    ) {
    }

    public static function fromModel(Wallet $wallet): self
    {
        return new self(
            $wallet->getKey(),
            $wallet->currency,
            (string) $wallet->balance,
            WalletDenominationResource::collect($wallet->denominations)->all()
        );
    }
}
