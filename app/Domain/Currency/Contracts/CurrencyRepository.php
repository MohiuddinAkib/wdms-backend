<?php

namespace App\Domain\Currency\Contracts;

use Illuminate\Support\Collection;
use App\Domain\Currency\Resources\CurrencyData;
use App\Domain\Currency\Resources\DenominationData;

interface CurrencyRepository
{
    /**
     * @return Collection<CurrencyData>
     */
    public function getCurrencies(): Collection;

    /**
     * @return Collection<DenominationData>
     */
    public function getDenominations(string $currency): Collection;
}
