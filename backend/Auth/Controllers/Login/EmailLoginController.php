<?php

namespace App\Modules\Auth\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\Login\LoginEmailRequest;
use App\Modules\Auth\Services\Login\EmailLoginService;
use App\Modules\Auth\Services\Login\Exceptions\FailedLoginException;
use App\Modules\Base\Exceptions\RateLimitException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class EmailLoginController extends Controller
{
    public function __construct(
        private readonly EmailLoginService $service,
    ) {}

    public function login(LoginEmailRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $result = $this->service->login($request->transform());

            return response()->json($result);
        } catch (RateLimitException|FailedLoginException $e) {
            return $this->defaultErrorResponse($e->getMessage(), $e->getCode());
        } catch (Throwable $throwable) {
            return $this->defaultErrorResponse();
        }
    }
}
