<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Currency\Contracts\CurrencyRepository;
use App\Domain\Wallet\Projections\Wallet;
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
        'currency' => [
          'required', 
          function(string $attribute, string $value, Closure $fail) use ($repository) {
              if(!$repository->isCurrencySupported($value)) {
                  $fail('Currency not supported');
              }
          },
          function(string $attribute, string $value, Closure $fail) use ($repository) {
            /** @var Wallet|null */
            $wallet = Wallet::where('currency', $value)->first();

            if(!is_null($wallet)) 
            { 
              $fail('Wallet already exists with the currency: ' .  $value);
            }
          }
        ]
      ];
    }
}
