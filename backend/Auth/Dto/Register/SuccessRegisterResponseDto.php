<?php

namespace App\Modules\Auth\Dto\Register;

use Spatie\LaravelData\Data;

class SuccessRegisterResponseDto extends Data
{
    public function __construct(
        public int $user_id,
        public int $next_request_seconds,
    ) {}
}
