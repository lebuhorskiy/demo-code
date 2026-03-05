<?php

namespace App\Modules\Auth\Services\Register;

use App\Modules\Auth\Dto\Register\EmailRegisterDto;
use App\Modules\Auth\Dto\Register\SuccessRegisterResponseDto;
use App\Modules\Auth\Dto\Register\VerifyEmailDto;
use App\Modules\Auth\Dto\Register\VerifyResponseDto;
use App\Modules\Auth\Events\UserVerifiedEvent;
use App\Modules\Auth\Helpers\CodeHelper;
use App\Modules\Auth\Helpers\RateLimiterHelper;
use App\Modules\Auth\Helpers\UsernameHelper;
use App\Modules\Auth\Mails\SendEmailVerificationMail;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Register\Pipes\Register\CheckAlreadyRegisteredPipe;
use App\Modules\Auth\Services\Register\Pipes\Verify\CheckAlreadyConfirmedPipe;
use App\Modules\Auth\Services\Register\Pipes\Verify\RateLimitPipe;
use App\Modules\Auth\Services\Register\Pipes\Register\RateLimitPipe as RegisterRateLimitPipe;
use App\Modules\Auth\Services\Register\Pipes\Verify\VerifyCodePipe;
use App\Modules\Currency\Repositories\CurrencyRepository;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailRegisterService
{
    public function __construct(
        private readonly Pipeline $pipeline,
        private readonly UserRepository $userRepository,
        private readonly CurrencyRepository $currencyRepository,
    ) {}

    public function register(EmailRegisterDto $payload): SuccessRegisterResponseDto
    {
        $this->pipeline->send($payload)
            ->through([
                RegisterRateLimitPipe::class,
                CheckAlreadyRegisteredPipe::class,
            ])
            ->thenReturn();

        if (!$user = $this->userRepository->findByConditions(['email' => $payload->email])) {
            $user = $this->userRepository->create([
                'email' => $payload->email,
                'referral_id' => $payload->referral_id,
                'currency_id' => $payload->currency_id ?? $this->currencyRepository->getPrimary()?->id,
                'password' => Hash::make($payload->password),
                'name' => UsernameHelper::generateUsernameFromEmail($payload->email),
            ]);
        }

        RateLimiterHelper::handle('register:re-send:short:', $payload->email, "Следующий код можно отправить через %s сек.", 3);

        $code = CodeHelper::generateRandomConfirmationCode(6);

        $cacheKey = 'verification_code:' . $user->id;
        Cache::put($cacheKey, Hash::make($code), now()->addMinutes(60));

        Mail::to($payload->email)->queue(new SendEmailVerificationMail($user, $code));

        return SuccessRegisterResponseDto::from([
            'user_id' => $user->id,
            'next_request_seconds' => 5 * 60,
        ]);
    }

    public function verify(VerifyEmailDto $payload): VerifyResponseDto
    {
        $this->pipeline->send($payload)
            ->through([
                RateLimitPipe::class,
                VerifyCodePipe::class,
                CheckAlreadyConfirmedPipe::class,
            ])
            ->thenReturn();

        $user = $this->userRepository->findByConditions([
            'id' => $payload->user_id,
        ], true);

        $user->update([
            'email_verified_at' => now(),
        ]);

        RateLimiterHelper::clear('verify:send:short:', $payload->user_id);
        Cache::forget('verification_code:' . $payload->user_id);

        event(new UserVerifiedEvent($user->fresh()));

        $token = auth('api')->login($user);

        return VerifyResponseDto::from([
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
