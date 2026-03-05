<?php

namespace App\Modules\Auth\Dto\Register;

use Spatie\LaravelData\Data;

class VerifyEmailDto extends Data
{
    public function __construct(
        public int $user_id,
        public string $code,
    ) {}
}
