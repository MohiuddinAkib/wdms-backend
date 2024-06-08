<?php

namespace App\Domain\User;

use App\Domain\Auth\Dto\RegisterUserData;
use App\Domain\User\Events\UserRegistered;
use App\Domain\User\Exceptions\UserAlreadyRegisteredException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class UserAggregate extends AggregateRoot
{
    private bool $created = false;

    public function register(RegisterUserData $data): self
    {
        throw_if($this->created, UserAlreadyRegisteredException::class);

        $this->recordThat(new UserRegistered(
            userId: $this->uuid(),
            name: $data->name,
            email: $data->email,
            password: $data->password
        ));

        return $this;
    }

    protected function applyUserRegistered(UserRegistered $event): void
    {
        $this->created = true;
    }
}
