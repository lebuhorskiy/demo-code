<?php

namespace App\Modules\Auth\Services\Register\Pipes\Register;

use App\Modules\Auth\Dto\Register\EmailRegisterDto;
use App\Modules\Auth\Helpers\RateLimiterHelper;
use App\Modules\Auth\Services\Register\Exceptions\FailedRegisterException;
use App\Modules\Base\Exceptions\RateLimitException;
use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class RateLimitPipe
{
    /**
     * @throws RateLimitException
     */
    public function handle(EmailRegisterDto $payload, Closure $next)
    {
        RateLimiterHelper::handle('register:send:short:', $payload->email, "Слишком много частых попыток регистрации на один email. Повторите через %s сек.");

        return $next($payload);
    }
}
