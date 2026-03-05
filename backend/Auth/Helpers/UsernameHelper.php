<?php

namespace App\Modules\Auth\Helpers;

use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Str;

class UsernameHelper
{
    public static function generateUsernameFromEmail(string $email): string
    {
        $base = Str::of(Str::before($email, '@'))
            ->lower()
            ->replace(['.', '+'], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->trim('_');

        if ($base->isEmpty()) {
            $base = 'user';
        }

        $candidate = (string) $base;

        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);

        $taken = $repository->getByConditions([
            'name' => $base,
        ])
            ->pluck('name')
            ->all();

        if (!in_array($candidate, $taken, true)) {
            return $candidate;
        }

        $i = 1;
        do {
            $candidate = $base.'_'.$i++;
        } while (in_array($candidate, $taken, true));

        return $candidate;
    }

    public static function normalizeUsername(string $username, int $size = 32): string
    {
        return (string) Str::of($username)
            ->trim()
            ->squish()
            ->ascii()
            ->lower()
            ->replace([' ', '-'], '_')
            ->replaceMatches('/[^a-z0-9_.]/', '')
            ->replaceMatches('/[_.]{2,}/', '_')
            ->trim('_.')
            ->limit($size, '');
    }
}
