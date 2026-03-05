<?php

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Events\UserRegisteredEvent;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Vip\Services\UserVipLevelEntity;

class UserRegisteredListener
{
    /**
     * Handle the event.
     */
    public function handle(UserRegisteredEvent $event): void
    {
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $ip = request()->header('CF-Connecting-IP') ?: request()->getClientIp();

        $repository->updateById($event->user->id, [
            'register_ip' => $ip,
        ]);

        app(UserVipLevelEntity::class, ['user_id' => auth()->id()]);
    }
}
