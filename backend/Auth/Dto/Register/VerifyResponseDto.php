<?php

namespace App\Modules\Auth\Dto\Register;

use Spatie\LaravelData\Data;

class VerifyResponseDto extends Data
{
    public function __construct(
        public string $access_token,
        public int $expires_in,
    ) {}
}
