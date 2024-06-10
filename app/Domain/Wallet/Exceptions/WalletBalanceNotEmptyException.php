<?php

namespace App\Domain\Wallet\Exceptions;

use App\Support\DomainException;
use App\Domain\Wallet\Resources\DeleteWalletResponseResource;

class WalletBalanceNotEmptyException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Wallet balance is not empty.");
    }

    public function render(): DeleteWalletResponseResource
    {
        return new DeleteWalletResponseResource(
            false,
            $this->getMessage()
        );
    }
}
