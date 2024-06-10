<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Resource;

class WalletListResponseResource extends Resource
{
    /**
     * @param  array<int, WalletResource>  $data
     */
    public function __construct(
        public bool $success,
        public array $data
    ) {
    }
}
