<?php

namespace App\Domain\Currency\Contracts;

use Illuminate\Support\Collection;
use App\Domain\Currency\Resources\CurrencyResource;
use App\Domain\Currency\Resources\DenominationResource;

interface CurrencyRepository
{
    /**
     * @return Collection<CurrencyResource>
     */
    public function getCurrencies(): Collection;

    /**
     * @return Collection<DenominationResource>
     */
    public function getDenominations(string $currency): Collection;

    public function isCurrencySupported(string $currency): bool;
}
