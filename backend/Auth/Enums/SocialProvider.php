<?php

namespace App\Modules\Auth\Enums;

enum SocialProvider: string
{
    case Google = 'google';
    case Telegram = 'telegram';
}
