<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Currency\Contracts\CurrencyRepository;
use App\Domain\Currency\Projections\Denomination;
use Closure;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Illuminate\Database\Query\Builder;

class AddWalletDenominationData extends Data
{
    #[FromRouteParameterProperty('wallet')]
    public string $currency;
    
    public function __construct(
      public string $name,
      public string $type
    ) {}

    public static function rules(ValidationContext $context, CurrencyRepository $currencyRepository)
    {
      return [
        'name' => [
          'required', 
          function(string $attribute, string $value, Closure $fail) use ($context, $currencyRepository) {
            $currency = $context->payload['currency'];

            $isValid = $currencyRepository->isValidDenomination($currency, $value, $context->payload['type']);

            if(!$isValid) {
              $fail("Invalid denomination {$value} for currency {$currency}.");
            }
          },
          Rule::unique(Denomination::class)->where(fn (Builder $query) => $query->where('type', $context->payload['type']))
        ],
        'type' => ['required', Rule::in(['coin', 'bill'])],
      ];
    }

    public static function messages(...$args)
    {
      return [
        'name.unique' => 'The denomination has already been taken.'
      ];
    }
}