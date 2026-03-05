<?php

namespace App\Modules\Auth\Dto\Login;

use Spatie\LaravelData\Data;

class EmailLoginResponseDto extends Data
{
    public function __construct(
        public string $access_token,
        public int $expires_in,
    ) {}
}
