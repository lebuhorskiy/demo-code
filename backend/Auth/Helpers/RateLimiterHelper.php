<?php

namespace App\Modules\Auth\Helpers;

use App\Modules\Base\Exceptions\RateLimitException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class RateLimiterHelper
{
    /**
     * @throws RateLimitException
     */
    public static function handle(string $key, string $value, string $errorMessage, int $shortAttempts = 3): void
    {
        $value = Str::lower($value);
        $ip = request()->header('CF-Connecting-IP') ?: request()->getClientIp();

        $keyShort = $key . sha1($value . '|' . $ip);
        $keyLong  = $key  . sha1($value . '|' . $ip);

        if (RateLimiter::tooManyAttempts($keyShort, $shortAttempts)) {
            $sec = RateLimiter::availableIn($keyShort);

            throw new RateLimitException(sprintf($errorMessage, $sec));
        }
        if (RateLimiter::tooManyAttempts($keyLong, 10)) {
            $sec = RateLimiter::availableIn($keyLong);

            throw new RateLimitException(sprintf($errorMessage, $sec));
        }

        RateLimiter::hit($keyShort, 60 * 5);
        RateLimiter::hit($keyLong, 3600);
    }

    public static function clear(string $key, string $value): void
    {
        $value = Str::lower($value);
        $ip = request()->header('CF-Connecting-IP') ?: request()->getClientIp();
        $keyShort = $key . sha1($value . '|' . $ip);
        $keyLong  = $key  . sha1($value . '|' . $ip);

        RateLimiter::clear($keyLong);
        RateLimiter::clear($keyShort);
    }
}
