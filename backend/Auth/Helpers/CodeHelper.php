<?php

namespace App\Modules\Auth\Helpers;

class CodeHelper
{
    public static function generateRandomConfirmationCode(int $length = 6): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
