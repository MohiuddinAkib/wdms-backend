<?php

namespace App\Domain\Wallet\Resources;

use App\Domain\Currency\Projections\Denomination;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapOutputName(SnakeCaseMapper::class)]
class WalletDenominationResource extends Resource
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public int $quantity
    ) {

    }

    public static function fromModel(Denomination $denomination): self
    {
        return new self(
            id: $denomination->getKey(),
            name: $denomination->name,
            type: $denomination->type,
            quantity: $denomination->quantity
        );
    }
}
