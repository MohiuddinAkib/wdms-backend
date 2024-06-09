<?php

namespace App\Domain\Contracts;

use App\Domain\Currency\Resource\CurrencyData;
use Illuminate\Support\Collection;

interface CurrencyRepository
{
    /**
     * @return Collection<CurrencyData>
     */
    public function getCurrencies(): Collection;
}
