<?php

namespace App\Http\Controllers;

use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Dto\AddWalletDenominationRequestData;
use App\Domain\Wallet\Dto\RemoveWalletDenominationData;
use App\Domain\Wallet\Exceptions\WalletDenominationBalanceExistsException;
use App\Domain\Wallet\Projections\Denomination;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\Resources\AddWalletDenominationResponseResource;
use App\Domain\Wallet\Resources\DeleteWalletDenominationResponseResource;
use App\Domain\Wallet\Resources\WalletResource;
use App\Domain\Wallet\WalletAggregateRoot;
use Cache;
use Str;

class WalletDenominationController extends Controller
{
    public function store(Wallet $wallet, AddWalletDenominationRequestData $data): AddWalletDenominationResponseResource
    {
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: $data->name,
                value: $data->value,
                type: $data->type,
            ))
            ->persist();

        // Invalidate wallet cache due to mutation
        Cache::tags(['wallets', auth()->id()])->flush();

        $wallet->refresh();

        return new AddWalletDenominationResponseResource(
            true,
            WalletResource::from($wallet)
        );
    }

    public function destroy(Wallet $wallet, Denomination $denomination): DeleteWalletDenominationResponseResource
    {
        throw_if($denomination->quantity > 0, WalletDenominationBalanceExistsException::class);

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->removeDenomination(
                new RemoveWalletDenominationData(
                    $denomination->getKey(),
                    $denomination->name,
                    $denomination->type,
                    $denomination->quantity
                )
            )
            ->persist();

        // Invalidate wallet cache due to mutation
        Cache::tags(['wallets', auth()->id()])->flush();

        return new DeleteWalletDenominationResponseResource(
            true,
            'Denomination removed successfully'
        );
    }
}
