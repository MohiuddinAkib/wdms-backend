<?php

namespace App\Domain\Currency\Repositories;

use App\Domain\Currency\Contracts\CurrencyRepository as ContractsCurrencyRepository;
use Illuminate\Support\Collection;
use App\Domain\Currency\Resources\CurrencyData;
use App\Domain\Currency\Resources\DenominationData;

class CurrencyRepository implements ContractsCurrencyRepository
{
    public function getCurrencies(): Collection
    {
        return collect(config('wallet.currencies', []))
            ->map(fn (array $entry, string $code) => new CurrencyData(
                $code,
                data_get($entry, 'name')
            ))
            ->values();
    }

    public function getDenominations(string $currency): Collection
    {
        /**
         * @param array{name: string, value: float|int} $denomination
         */
        $coins = collect(config("wallet.currencies.{$currency}.coins", []))
            ->map(fn (array $denomination) => new DenominationData(
                name: $denomination['name'],
                value: $denomination['value'],
                type: 'coin'
            ));

        /**
         * @param array{name: string, value: float|int} $denomination
         */
        $bills = collect(config("wallet.currencies.{$currency}.bills", []))
            ->map(fn (array $denomination) => new DenominationData(
                name: $denomination['name'],
                value: $denomination['value'],
                type: 'bill'
            ));

        return $coins->merge($bills)->values()->unique(fn (DenominationData $denomination) => $denomination->name . $denomination->type . $denomination->value);
    }
}
