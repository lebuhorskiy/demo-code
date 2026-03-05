<?php

namespace App\Modules\Auth\Services\Register\Pipes\Register;

use App\Modules\Auth\Dto\Register\EmailRegisterDto;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Register\Exceptions\FailedRegisterException;
use Closure;

class CheckAlreadyRegisteredPipe
{
    /**
     * @throws FailedRegisterException
     */
    public function handle(EmailRegisterDto $payload, Closure $next): EmailRegisterDto
    {
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $user = $repository->findByConditions([
            'email' => $payload->email,
        ], true);

        if (!$user) {
            return $next($payload);
        }

        if ($user->email_verified_at !== null) {
            throw new FailedRegisterException('User with this email is already registered.');
        }

        return $next($payload);
    }
}
