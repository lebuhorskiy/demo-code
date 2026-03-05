<?php

namespace App\Modules\Auth\Services\Login;

use App\Modules\Auth\Dto\Login\EmailLoginDto;
use App\Modules\Auth\Dto\Login\EmailLoginResponseDto;
use App\Modules\Auth\Services\Login\Exceptions\FailedLoginException;
use App\Modules\Auth\Services\Login\Pipes\CheckAvailableUserPipe;
use App\Modules\Auth\Services\Login\Pipes\CheckExistsUserPipe;
use App\Modules\Auth\Services\Login\Pipes\RateLimitPipe;
use Illuminate\Pipeline\Pipeline;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmailLoginService
{
    public function __construct(
        private readonly Pipeline $pipeline,
    ) {}

    /**
     * @throws FailedLoginException
     */
    public function login(EmailLoginDto $payload): EmailLoginResponseDto
    {
        $this->pipeline->send($payload)
            ->through([
                RateLimitPipe::class,
                CheckExistsUserPipe::class,
                CheckAvailableUserPipe::class,
            ])
            ->thenReturn();

        if (!$token = JWTAuth::attempt($payload->toArray())) {
            throw new FailedLoginException('Invalid credentials', 401);
        }

        return EmailLoginResponseDto::from([
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
