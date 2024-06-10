<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DenominationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletDenominationController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        // AFTER GETTING OTP TOKEN CAN REQUEST ACCESS TOKEN OR AUTH SESSION
        Route::post('login', [AuthController::class, 'login'])->name('login');
        // FOR MULTI FACTOR FIRST NEEDS TO GET OTP TOKEN
        Route::post('request-otp', [AuthController::class, 'requestOtp'])->name('request-otp');
    });

Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
Route::get('currencies/{currency}/denominations', [DenominationController::class, 'index'])->name('denominations.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(WalletController::class)->group(function () {
        Route::post('wallets', 'store')->name('wallets.store');
        Route::get('wallets', 'index')->name('wallets.index');
        Route::delete('wallets/{wallet}', 'destroy')
            ->name('wallets.destroy')
            ->can('delete-wallet', 'wallet');
        Route::get('wallets/{wallet}', 'show')
            ->name('wallets.show')
            ->can('show-wallet', 'wallet');
    });

    Route::controller(WalletDenominationController::class)
        ->prefix('wallets/{wallet}')
        ->name('wallet-denominations.')
        ->group(function () {
            Route::post('denominations', 'store')
                ->name('store')
                ->can('update-wallet', 'wallet');

            Route::delete('denominations/{denomination}', 'destroy')
                ->name('destroy')
                ->can('remove-wallet-denomination', 'wallet,denomination');
        });

    Route::controller(TransactionController::class)
        ->prefix('transactions/{wallet}')
        ->name('transactions.')
        ->group(function() {
            Route::post('/', 'store')->name('store')
            ->can('update-wallet', 'wallet');
        });
});
