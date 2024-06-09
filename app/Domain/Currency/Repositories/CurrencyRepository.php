<?php

namespace App\Domain\Currency\Repositories;

use App\Domain\Contracts\CurrencyRepository as ContractsCurrencyRepository;
use App\Domain\Currency\Resource\CurrencyData;
use Illuminate\Support\Collection;

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
}
