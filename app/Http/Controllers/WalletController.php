<?php

namespace App\Http\Controllers;

use App\Domain\Wallet\Dto\CreateWalletData;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\Resources\CreateWalletResponseResource;
use App\Domain\Wallet\Resources\WalletResource;
use App\Domain\Wallet\WalletAggregate;
use Str;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateWalletData $data): CreateWalletResponseResource
    {
        $walletId = (string)Str::uuid();
        
        WalletAggregate::retrieve($walletId)
            ->createWallet(auth()->user()->uuid, $data->currency)
            ->persist();

        $createdWallet = Wallet::find($walletId);

        if (is_null($createdWallet)) {
            return new CreateWalletResponseResource(
                success: false,
                message: 'Failed to create wallet',
                data: null
            );
        }

        $createdWalletResource = WalletResource::from($createdWallet);

        return new CreateWalletResponseResource(
            success: true,
            message: 'Wallet created successfully',
            data: $createdWalletResource
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}
