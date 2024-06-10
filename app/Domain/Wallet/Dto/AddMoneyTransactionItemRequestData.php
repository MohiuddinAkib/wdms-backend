<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Wallet\Projections\Denomination;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class AddMoneyTransactionItemRequestData extends Data
{
    public function __construct(
        public string $denominationId,
        public int $quantity
    ) {
    }

    public static function rules(ValidationContext $context)
    {
        return [
            'denomination_id' => [
                'required',
                Rule::exists(Denomination::class, 'uuid')
                    ->where(fn (Builder $query) => $query->where('wallet_id', $context->fullPayload['uuid'])),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
