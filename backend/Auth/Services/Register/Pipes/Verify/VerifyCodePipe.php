<?php

namespace App\Modules\Auth\Services\Register\Pipes\Verify;

use App\Modules\Auth\Dto\Register\VerifyEmailDto;
use App\Modules\Auth\Services\Register\Exceptions\FailedVerifyException;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class VerifyCodePipe
{
    /**
     * @throws FailedVerifyException
     */
    public function handle(VerifyEmailDto $payload, Closure $next): VerifyEmailDto
    {
        $cacheKey = 'verification_code:' . $payload->user_id;
        $hash = Cache::get($cacheKey);

        $fail = function () {
            throw new FailedVerifyException('Код неверный или истёк срок его действия');
        };

        if (!$hash) {
            $fail();
        }

        if (!Hash::check($payload->code, $hash)) {
            $fail();
        }

        return $next($payload);
    }
}
