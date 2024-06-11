<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Wallet\Projections\Denomination;
use Closure;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class WithdrawMoneyTransactionItemRequestData extends Data
{
    public function __construct(
      public string $denominationId,
      public int $quantity,
    ) {}

    public static function rules(ValidationContext $context)
    {
      return [
        'denomination_id' => ['required', Rule::exists(Denomination::class, 'uuid')->where('wallet_id', $context->fullPayload['uuid'])],
        'quantity' => ['required', 'integer', 'min:1', function(string $attribute, int $value, Closure $fail) use($context) {
            $denomination = Denomination::where('wallet_id', $context->fullPayload['uuid'])
              ->find($context->payload['denomination_id']);

            if(!is_null($denomination)) {
              if($denomination->quantity < $value) {
                $fail('Not enough balance.');
              }
            }
        }]
      ];
    }
}
