<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Domain\Wallet\Projections\Wallet;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DenominationController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

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

Route::get('currencies', [CurrencyController::class, 'index'])->name('currency.index');
Route::get('currencies/{currency}/Denominations', [DenominationController::class, 'index'])->name('denomination.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(WalletController::class)->group(function() {
        Route::post('wallets', 'store')->name('wallet.store');
        Route::delete('wallets/{wallet}', 'destroy')
                ->name('wallet.destroy')
                ->can('delete-wallet', 'wallet');
    });
});
