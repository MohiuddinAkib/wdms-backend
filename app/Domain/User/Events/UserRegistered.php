<?php

namespace App\Domain\User\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class UserRegistered extends ShouldBeStored
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
        public string $password
    )
    {
    }
}
