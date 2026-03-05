<?php

namespace App\Modules\Auth\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\Register\EmailRegisterRequest;
use App\Modules\Auth\Requests\Register\EmailVerifyRequest;
use App\Modules\Auth\Services\Register\EmailRegisterService;
use App\Modules\Auth\Services\Register\Exceptions\FailedRegisterException;
use App\Modules\Auth\Services\Register\Exceptions\FailedVerifyException;
use App\Modules\Base\Exceptions\RateLimitException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class EmailRegisterController extends Controller
{
    public function __construct(
        private readonly EmailRegisterService $service,
    ) {}

    public function register(EmailRegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = $this->service->register($request->transform());

            DB::commit();

            return response()->json($result->toArray());
        } catch (FailedRegisterException|RateLimitException $exception) {
            DB::rollBack();

            return $this->defaultErrorResponse($exception->getMessage());
        } catch (Throwable $exception) {
            dd($exception);
            DB::rollBack();

            return $this->defaultErrorResponse();
        }
    }

    public function verify(EmailVerifyRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = $this->service->verify($request->transform());

            DB::commit();

            return response()->json($result->toArray());
        } catch (FailedVerifyException|RateLimitException $exception) {
            DB::rollBack();

            return $this->defaultErrorResponse($exception->getMessage());
        } catch (Throwable $exception) {
            DB::rollBack();

            return $this->defaultErrorResponse();
        }
    }
}
