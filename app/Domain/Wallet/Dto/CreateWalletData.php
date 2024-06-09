<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Currency\Contracts\CurrencyRepository;
use Closure;
use Spatie\LaravelData\Data;

class CreateWalletData extends Data
{
    public function __construct(
      public string $currency
    ) {}

    public static function rules(): array
    {
      $repository = app(CurrencyRepository::class);
      return [
        'currency' => ['required', function(string $attribute, string $value, Closure $fail) use ($repository) {
            if(!$repository->isCurrencySupported($value)) {
                $fail('Currency not supported');
            }
        }]
      ];
    }
}
