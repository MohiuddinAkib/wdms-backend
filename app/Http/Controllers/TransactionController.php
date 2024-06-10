<?php

namespace App\Http\Controllers;

use App\Domain\Currency\Contracts\CurrencyRepository;
use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use App\Domain\Wallet\Dto\AddMoneyTransactionItemData;
use App\Domain\Wallet\Dto\AddMoneyTransactionItemRequestData;
use App\Domain\Wallet\Dto\AddMoneyTransactionRequestData;
use App\Domain\Wallet\Projections\Denomination;
use App\Domain\Wallet\Projections\Transaction;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\Resources\AddMoneyTransactionResponseResource;
use App\Domain\Wallet\Resources\WalletResource;
use App\Domain\Wallet\WalletAggregateRoot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Str;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Wallet $wallet, AddMoneyTransactionRequestData $data, CurrencyRepository $currencyRepository)
    {
        $denominationIds = collect($data->denominations)
                ->map(fn (AddMoneyTransactionItemRequestData $denomination) => $denomination->denominationId)
                ->values();

        $denominationIdQuantityMapping = collect($data->denominations)
            ->mapWithKeys(fn (AddMoneyTransactionItemRequestData $denomination) => [$denomination->denominationId => $denomination->quantity])
            ->all();

        /** @var Collection<int, Denomination> */
        $denominations = Denomination::whereIn('uuid', $denominationIds)
                        ->where('wallet_id', $wallet->getKey())
                        ->get();
        
        $dtos = $denominations
                ->map(function(Denomination $denomination) use($denominationIdQuantityMapping) {
                    return new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        $denomination->getKey(),
                        $denomination->type,
                        $denomination->value,
                        $denominationIdQuantityMapping[$denomination->getKey()]
                    );
                })
                ->values();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $dtos->all()
            ))
            ->persist();

        $wallet->refresh();

        return new AddMoneyTransactionResponseResource(
            true,
            "Money added successfully.",
            WalletResource::from($wallet)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
