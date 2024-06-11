<?php

namespace App\Domain\Wallet\Resources;

use App\Domain\Wallet\Projections\Transaction;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapName(SnakeCaseMapper::class)]
class TransactionResource extends Resource
{
    public function __construct(
        public string $id,
        public WalletResource $wallet,
        public WalletDenominationResource $denomination,
        public string $type,
        public int $quantity,
        public Carbon $happenedAt,
    ) {
    }

    public static function fromModel(Transaction $transaction): self
    {
        return new self(
            $transaction->getKey(),
            WalletResource::from($transaction->wallet)->exclude('denominations'),
            WalletDenominationResource::from($transaction->denomination),
            $transaction->type,
            $transaction->quantity,
            $transaction->happened_at,
        );
    }
}
