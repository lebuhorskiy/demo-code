<?php

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Events\UserRegisteredEvent;
use App\Modules\Auth\Repositories\UserRepository;

class UserLoginListener
{
    public function handle(UserRegisteredEvent $event): void
    {
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $repository->updateById($event->user->id, [
            'last_login' => now(),
            'last_activity' => now(),
        ]);
    }
}
