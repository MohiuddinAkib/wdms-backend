<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DenominationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletDenominationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ENDPOINT TO GET PROFILE INFO
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ENDPOINTS TO LOGIN, REGISTER AND REQUEST OTP
Route::prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        // AFTER GETTING OTP TOKEN CAN REQUEST ACCESS TOKEN OR AUTH SESSION
        Route::post('login', [AuthController::class, 'login'])->name('login');
        // FOR MULTI FACTOR FIRST NEEDS TO GET OTP TOKEN
        Route::post('request-otp', [AuthController::class, 'requestOtp'])->name('request-otp');
    });

// ENDPOINTS TO LOGOUT
Route::post('auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('auth.logout');

// ENDPOINTS TO GET CURRENCIES
Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
// ENDPOINTS TO GET DENOMINATIONS FOR A CURRENCY
Route::get('currencies/{currency}/denominations', [DenominationController::class, 'index'])->name('denominations.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(WalletController::class)->group(function () {
        // ENDPOINT TO ADD WALLET
        Route::post('wallets', 'store')->name('wallets.store');
        // ENDPOINT TO GET LIST OF WALLET
        Route::get('wallets', 'index')->name('wallets.index');
        // ENDPOINT TO GET DELETE OF WALLET
        Route::delete('wallets/{wallet}', 'destroy')
            ->name('wallets.destroy')
            ->can('delete-wallet', 'wallet');
        // ENDPOINT TO SEE WALLET DETAILS
        Route::get('wallets/{wallet}', 'show')
            ->name('wallets.show')
            ->can('show-wallet', 'wallet');
    });

    Route::controller(WalletDenominationController::class)
        ->prefix('wallets/{wallet}')
        ->name('wallet-denominations.')
        ->group(function () {
            // ENDPOINT TO ADD WALLET DENOMINATION
            Route::post('denominations', 'store')
                ->name('store')
                ->can('update-wallet', 'wallet');

            // ENDPOINT TO REMOVE WALLET DENOMINATION
            Route::delete('denominations/{denomination}', 'destroy')
                ->name('destroy')
                ->can('remove-wallet-denomination', 'wallet,denomination');
        });

    Route::controller(TransactionController::class)
        ->prefix('transactions')
        ->name('transactions.')
        ->group(function () {
            // ENDPOINT TO ADD MONEY TO WALLET
            Route::post('{wallet}/deposit', 'deposit')->name('deposit')
                ->can('update-wallet', 'wallet');

            // ENDPOINT TO WITHDRAW MONEY TO WALLET
            Route::post('{wallet}/withdraw', 'withdraw')->name('withdraw')
                ->can('withdraw-wallet', 'wallet');

            // ENDPOINT TO GET TRANSACTION HISTORY
            Route::get('/', 'index')->name('index');
        });
});