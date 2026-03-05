<?php

namespace App\Modules\Auth\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Enums\SocialProvider;
use App\Modules\Auth\Services\Register\Exceptions\FailedSocialAuthException;
use App\Modules\Auth\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialRegisterController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $service,
    ) {}

    public function start(string $provider): JsonResponse
    {
        try {
            $redirectUrl = Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'redirect_url' => $redirectUrl,
            ]);
        } catch (Throwable $throwable) {
            return $this->defaultErrorResponse();
        }
    }

    public function callback(string $provider): JsonResponse
    {
        DB::beginTransaction();

        try {
            $result = $this->service->callback(SocialProvider::from($provider));

            DB::commit();

            return response()->json($result);
        } catch (FailedSocialAuthException $exception) {
            return $this->defaultErrorResponse($exception->getMessage(), $exception->getCode());
        } catch (Throwable $throwable) {
            DB::rollBack();

            return $this->defaultErrorResponse();
        }
    }
}
