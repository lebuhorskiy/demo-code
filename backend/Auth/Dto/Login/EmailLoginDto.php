<?php

namespace App\Modules\Auth\Dto\Login;

use Spatie\LaravelData\Data;

class EmailLoginDto extends Data
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
