<?php

namespace App\Http\Controllers;

use App\Domain\Wallet\Dto\CreateWalletData;
use App\Domain\Wallet\Exceptions\WalletBalanceNotEmptyException;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\Resources\CreateWalletResponseResource;
use App\Domain\Wallet\Resources\DeleteWalletResponseResource;
use App\Domain\Wallet\Resources\WalletDetailsResponseResource;
use App\Domain\Wallet\Resources\WalletListResponseResource;
use App\Domain\Wallet\Resources\WalletResource;
use App\Domain\Wallet\WalletAggregate;
use App\Models\User;
use Brick\Math\BigDecimal;
use Cache;
use Illuminate\Http\Request;
use Str;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): WalletListResponseResource
    {
        /**
         * @var User
         */
        $user = $request->user();

        // CACHE WITH TAG TO LATER MODIFY WHILE MUTATING THE DATA
        return Cache::tags(['wallets', auth()->id()])
            ->remember(
                'wallet-list',
                now()->addMinutes(5),
                fn () => new WalletListResponseResource(
                    true,
                    WalletResource::collect($user->wallets)->all()
                )
            );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateWalletData $data): CreateWalletResponseResource
    {
        $walletId = (string) Str::uuid();

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

        // Invalidate wallet cache due to mutation
        Cache::tags(['wallets', auth()->id()])->flush();

        return new CreateWalletResponseResource(
            success: true,
            message: 'Wallet created successfully',
            data: $createdWalletResource
        );
    }

    public function show(Wallet $wallet): WalletDetailsResponseResource
    {
        return Cache::tags(['wallets', auth()->id()])
            ->remember(
                'wallet-details-'.$wallet->getKey(),
                now()->addMinutes(5),
                fn () => new WalletDetailsResponseResource(
                    true,
                    WalletResource::from($wallet)
                )
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet): DeleteWalletResponseResource
    {
        // WILL NOT ALLOW TO DELETE WALLET WITH BALANCE
        throw_if(BigDecimal::of($wallet->balance)->compareTo(0) > 0, WalletBalanceNotEmptyException::class);

        WalletAggregate::retrieve($wallet->getKey())
            ->deleteWallet()
            ->persist();

        // Invalidate wallet cache due to mutation
        Cache::tags(['wallets', auth()->id()])->flush();

        return new DeleteWalletResponseResource(
            true,
            'Wallet deleted successfully.'
        );
    }
}
