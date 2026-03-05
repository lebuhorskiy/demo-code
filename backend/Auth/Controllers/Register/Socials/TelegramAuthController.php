<?php

namespace App\Modules\Auth\Controllers\Register\Socials;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Enums\SocialProvider;
use App\Modules\Auth\Requests\Register\Social\AuthTelegramRequest;
use App\Modules\Auth\Services\Register\Exceptions\FailedSocialAuthException;
use App\Modules\Auth\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class TelegramAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $service,
    ) {}
    public function callback(AuthTelegramRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $result = $this->service->createOrLoginSocialProvider(
                provider: SocialProvider::Telegram,
                name: $request->get('username'),
                id: $request->get('id'),
                extra: $request->all(),
            );

            DB::commit();

            return response()->json($result);
        } catch (FailedSocialAuthException $exception) {
            dd($exception);
            DB::rollBack();

            return $this->defaultErrorResponse($exception->getMessage(), $exception->getCode());
        } catch (Throwable $e) {
            dd($e);
            DB::rollBack();

            return $this->defaultErrorResponse();
        }
    }
}
