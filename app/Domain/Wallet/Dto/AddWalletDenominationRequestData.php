<?php

namespace App\Domain\Wallet\Dto;

use App\Domain\Currency\Contracts\CurrencyRepository;
use App\Domain\Wallet\Projections\Denomination;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class AddWalletDenominationRequestData extends Data
{
    #[FromRouteParameterProperty('wallet')]
    public string $currency;

    public function __construct(
        public string $name,
        public string $type,
        public float|int $value,
    ) {
    }

    public static function rules(ValidationContext $context, CurrencyRepository $currencyRepository)
    {
        return [
            'name' => [
                'required',
                function (string $attribute, string $value, Closure $fail) use ($context, $currencyRepository) {
                    $currency = $context->payload['currency'];

                    $isValid = $currencyRepository->isValidDenomination($currency, $value, $context->payload['type']);

                    if (! $isValid) {
                        $fail("Invalid denomination {$value} for currency {$currency}.");
                    }
                },
                Rule::unique(Denomination::class)->where(fn (Builder $query) => $query->where('type', $context->payload['type'])),
            ],
            'type' => ['required', Rule::in(['coin', 'bill'])],
            'value' => ['required'],
        ];
    }

    public static function messages(...$args)
    {
        return [
            'name.unique' => 'The denomination has already been taken.',
        ];
    }
}
