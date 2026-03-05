<?php

namespace App\Modules\Auth\Services\Register\Pipes\Verify;

use App\Modules\Auth\Dto\Register\VerifyEmailDto;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Register\Exceptions\FailedVerifyException;
use Closure;

class CheckAlreadyConfirmedPipe
{
    /**
     * @throws FailedVerifyException
     */
    public function handle(VerifyEmailDto $payload, Closure $next)
    {
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $user = $repository->findByConditions([
            'id' => $payload->user_id,
        ]);

        if (!$user || $user->email_verified_at !== null) {
            throw new FailedVerifyException('Почта уже подтверждена для этого пользователя.');
        }

        return $next($payload);
    }
}
