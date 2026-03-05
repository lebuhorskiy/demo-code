<?php

namespace App\Modules\Auth\Services\Login\Pipes;

use App\Modules\Auth\Dto\Login\EmailLoginDto;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Login\Exceptions\FailedLoginException;
use Closure;

class CheckExistsUserPipe
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

        if (!$user) {
            throw new FailedLoginException('Invalid credentials.');
        }

        return $next($payload);
    }
}
