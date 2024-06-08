<?php

namespace App\Domain\User\Projectors;

use App\Models\User;
use App\Domain\User\Events\UserRegistered;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class UsersProjector extends Projector
{
    public function onUserRegistered(UserRegistered $event): void
    {
        User::create([
            'uuid' => $event->userId,
            'name' => $event->name,
            'email' => $event->email,
            'password' => $event->password
        ]);
    }
}
