<?php

namespace App\Http\Controllers;

use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\Resources\AddWalletDenominationResponseResource;
use App\Domain\Wallet\Resources\WalletResource;
use App\Domain\Wallet\WalletAggregate;
use Cache;
use Str;

class WalletDenominationController extends Controller
{
    public function store(Wallet $wallet, AddWalletDenominationData $data): AddWalletDenominationResponseResource
    {
        $denominationId = (string)Str::uuid();
        WalletAggregate::retrieve($wallet->getKey())
            ->addDenomination($denominationId, $data)
            ->persist();

        // Invalidate wallet cache due to mutation
        Cache::tags(['wallets', auth()->id()])->flush();

        $wallet->refresh();

        return new AddWalletDenominationResponseResource(
            true,
            WalletResource::from($wallet)
        );
    }
}
