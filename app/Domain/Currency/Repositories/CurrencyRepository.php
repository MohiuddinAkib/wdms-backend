<?php

namespace App\Domain\Currency\Repositories;

use App\Domain\Currency\Contracts\CurrencyRepository as ContractsCurrencyRepository;
use App\Domain\Currency\Resources\CurrencyResource;
use App\Domain\Currency\Resources\DenominationResource;
use Illuminate\Support\Collection;

class CurrencyRepository implements ContractsCurrencyRepository
{
    public function getCurrencies(): Collection
    {
        return collect(config('wallet.currencies', []))
            ->map(fn (array $entry, string $code) => new CurrencyResource(
                $code,
                data_get($entry, 'name')
            ))
            ->values();
    }

    public function getDenominations(string $currency): Collection
    {
        /**
         * @param  array{name: string, value: float|int}  $denomination
         */
        $coins = collect(config("wallet.currencies.{$currency}.coins", []))
            ->map(fn (array $denomination) => new DenominationResource(
                name: $denomination['name'],
                value: $denomination['value'],
                type: 'coin'
            ));

        /**
         * @param  array{name: string, value: float|int}  $denomination
         */
        $bills = collect(config("wallet.currencies.{$currency}.bills", []))
            ->map(fn (array $denomination) => new DenominationResource(
                name: $denomination['name'],
                value: $denomination['value'],
                type: 'bill'
            ));

        return $coins->merge($bills)->values()->unique(fn (DenominationResource $denomination) => $denomination->name.$denomination->type);
    }

    public function isCurrencySupported(string $currency): bool
    {
        return config()->has("wallet.currencies.{$currency}");
    }

    public function isValidDenomination(string $currency, string $denomination, string $type): bool
    {
        if (! $this->isCurrencySupported($currency)) {
            return false;
        }

        $denominaions = config("wallet.currencies.{$currency}.{$type}s");

        if (is_null($denominaions)) {
            return false;
        }

        foreach ($denominaions as $eachDenomination) {
            if (data_get($eachDenomination, 'name') === $denomination) {
                return true;
            }
        }

        return false;
    }
}
