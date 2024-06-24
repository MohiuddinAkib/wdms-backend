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
        public string $groupId,
        public string $walletId,
        public string $walletCurrency,
        public string $denominationId,
        public string $denominationType,
        public float|int $denominationValue,
        public string $type,
        public int $quantity,
        public Carbon $happenedAt,
    ) {
    }

    public static function fromModel(Transaction $transaction): self
    {
        return new self(
            $transaction->getKey(),
            $transaction->group_id,
            $transaction->wallet_id,
            $transaction->wallet_currency,
            $transaction->denomination_id,
            $transaction->denomination_type,
            $transaction->denomination_value,
            $transaction->type,
            $transaction->denomination_quantity,
            $transaction->happened_at,
        );
    }
}