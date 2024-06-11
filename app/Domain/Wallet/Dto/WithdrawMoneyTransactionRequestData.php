<?php

namespace App\Domain\Wallet\Dto;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class WithdrawMoneyTransactionRequestData extends Data
{
    #[FromRouteParameterProperty('wallet')]
    public string $uuid;

    public function __construct(
        #[Min(1)]
        #[DataCollectionOf(WithdrawMoneyTransactionItemRequestData::class)]
        public array $denominations
    ) {
    }

    public static function rules()
    {
        return [
            'denominations.*.denomination_id' => ['distinct:strict'],
        ];
    }
}
