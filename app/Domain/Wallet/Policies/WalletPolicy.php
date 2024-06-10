<?php

namespace App\Domain\Wallet\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Domain\Wallet\Projections\Wallet;

class WalletPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Wallet $wallet): bool
    {
        if($wallet->user->isNot($user))
        {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function show(User $user, Wallet $wallet): bool|Response
    {
        if($wallet->user->isNot($user))
        {
            return Response::denyAsNotFound();
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wallet $wallet): bool|Response
    {
        if($wallet->user->isNot($user))
        {
            return Response::denyAsNotFound();
        }

        return true;
    }
}
