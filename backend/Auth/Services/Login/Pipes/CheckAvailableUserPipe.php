<?php

namespace App\Modules\Auth\Services\Login\Pipes;

use App\Modules\Auth\Dto\Login\EmailLoginDto;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Login\Exceptions\FailedLoginException;
use Closure;

class CheckAvailableUserPipe
{

    /**
     * @throws FailedLoginException
     */
    public function handle(EmailLoginDto $payload, Closure $next)
    {
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $user = $repository->findByConditions([
            'email' => $payload->email,
        ]);

        if ($user->isBan()) {
            throw new FailedLoginException('Your account was suspended.');
        }

        return $next($payload);
    }
}
