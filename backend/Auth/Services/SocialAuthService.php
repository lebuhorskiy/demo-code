<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Dto\Register\VerifyResponseDto;
use App\Modules\Auth\Helpers\UsernameHelper;
use App\Modules\Auth\Repositories\SocialProviderRepository;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\Register\Exceptions\FailedSocialAuthException;
use App\Modules\Currency\Repositories\CurrencyRepository;
use App\Modules\User\Models\SocialProvider;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Modules\Auth\Enums\SocialProvider as SocialProviderType;

class SocialAuthService
{
    public function __construct(
        private readonly SocialProviderRepository $socialProviderRepository,
        private readonly UserRepository $userRepository,
        private readonly CurrencyRepository $currencyRepository,
    ) {}

    /**
     * @throws FailedSocialAuthException
     */
    public function callback(SocialProviderType $provider): VerifyResponseDto
    {
        $driver = Socialite::driver($provider->value);

        $socialiteUser = $driver
            ->stateless()
            ->user();

        $providerId = $socialiteUser->getId();

        $extra = [
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'expires_in' => $socialiteUser->expiresIn,
        ];

        if ($socialiteUser?->email) {
            $isExists = User::where('email', $socialiteUser?->email)->exists();
            if (!$isExists) {
                $extra['email'] = $socialiteUser?->email;
            }
        }

        return $this->createOrLoginSocialProvider(
            provider: $provider,
            name: UsernameHelper::normalizeUsername($socialiteUser->getNickname() ?? $socialiteUser->getName()),
            id: $providerId,
            avatar: $socialiteUser->getAvatar() ?? null,
            extra: $extra,
        );
    }

    /**
     * @throws FailedSocialAuthException
     */
    public function createOrLoginSocialProvider(SocialProviderType $provider, string $name, string $id, ?string $avatar = null, array $extra = []): VerifyResponseDto
    {
        Log::notice('callback user', [
            'extra' => $extra,
        ]);

        if (
            $socialProvider = $this->socialProviderRepository->findByConditions([
                'provider_name' => $provider->value,
                'provider_id' => $id,
            ])
        ) {
            return $this->login($socialProvider);
        }

        $base = $name;

        $i = 1;

        while ($this->userRepository->findByConditions(['name' => $name], true)) {
            $name = $base . ++$i;
        }

        $user = $this->userRepository->create([
            'name'        => $name,
            'email'       => array_key_exists('email', $extra) ? $extra['email'] : null,
            'email_verified_at' => array_key_exists('email_verified_at', $extra) ? now() : null,
            'currency_id' => request()->input('currency_id') ?? $this->currencyRepository->getPrimary()?->id,
            'referral_id' => request()->input('referral_id'),
        ]);

        if (!$user) {
            throw new FailedSocialAuthException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if ($avatar) {
            DB::afterCommit(function () use ($user, $avatar) {
                try {
                    $user->addMediaFromUrl($avatar)->toMediaCollection('avatars');
                } catch (Throwable $e) {}
            });
        }


        $makeSocialRecord = $this->socialProviderRepository->create([
            'provider_id'   => $id,
            'provider_name' => $provider->name,
            'name'          => $name,
            'extra'         => $extra,
            'user_id'       => $user->id,
        ]);

        return $this->login($makeSocialRecord);
    }

    /**
     * @throws FailedSocialAuthException
     */
    public function login(SocialProvider $provider): VerifyResponseDto
    {
        if ($provider->user->isBan()) {
            throw new FailedSocialAuthException('Your account was suspended.', Response::HTTP_FORBIDDEN);
        }

        if (!$token = auth()->login($provider->user)) {
            throw new FailedSocialAuthException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return VerifyResponseDto::from([
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
