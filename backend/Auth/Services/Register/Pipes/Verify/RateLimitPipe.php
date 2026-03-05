<?php

namespace App\Modules\Auth\Services\Register\Pipes\Verify;

use App\Modules\Auth\Dto\Register\VerifyEmailDto;
use App\Modules\Auth\Helpers\RateLimiterHelper;
use App\Modules\Base\Exceptions\RateLimitException;
use Closure;

class RateLimitPipe
{
    /**
     * @throws RateLimitException
     */
    public function handle(VerifyEmailDto $payload, Closure $next)
    {
        RateLimiterHelper::handle('verify:send:short:', $payload->user_id, "Слишком много попыток ввода кода. Повторите через %s сек.");

        return $next($payload);
    }
}
