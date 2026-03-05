<?php

namespace App\Modules\Auth\Services\Login\Pipes;

use App\Modules\Auth\Dto\Login\EmailLoginDto;
use App\Modules\Auth\Helpers\RateLimiterHelper;
use App\Modules\Base\Exceptions\RateLimitException;
use Closure;

class RateLimitPipe
{
    /**
     * @throws RateLimitException
     */
    public function handle(EmailLoginDto $payload, Closure $next)
    {
        RateLimiterHelper::handle('login:send:short:', $payload->email, "Слишком много попыток входа. Повторите через %s сек.");

        return $next($payload);
    }
}
