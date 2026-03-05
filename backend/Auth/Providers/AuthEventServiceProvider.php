<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Events\UserLoginEvent;
use App\Modules\Auth\Events\UserRegisteredEvent;
use App\Modules\Auth\Listeners\UserLoginListener;
use App\Modules\Auth\Listeners\UserRegisteredListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;

class AuthEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoginEvent::class => [
            UserLoginListener::class,
        ],
        UserRegisteredEvent::class => [
            UserRegisteredListener::class,
        ],
        Login::class => [
            UserLoginListener::class,
        ]
    ];
}
