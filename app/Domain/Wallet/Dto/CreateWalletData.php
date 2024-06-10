<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Currency\Contracts\CurrencyRepository;
use App\Domain\Wallet\Projections\Wallet;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

class CreateWalletData extends Data
{
    public function __construct(
        public string $currency
    ) {
    }

    public static function rules(CurrencyRepository $repository): array
    {
        return [
            'currency' => [
                'required',
                function (string $attribute, string $value, Closure $fail) use ($repository) {
                    if (! $repository->isCurrencySupported($value)) {
                        $fail('Currency not supported');
                    }
                },
                Rule::unique(Wallet::class)->where(fn(Builder $query) => $query->where('user_id', auth()->user()->uuid))
            ],
        ];
    }

    public static function messages(...$args)
    {
        return [
            'currency.unique' => 'Wallet already exists with the currency: :input'
        ];
    }
}
