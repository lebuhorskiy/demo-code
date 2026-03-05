<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\User\Models\SocialProvider;

class SocialProviderRepository
{
    public function findByConditions(array $conditions, bool $lock = false): ?SocialProvider
    {
        return SocialProvider::where($conditions)
            ->when($lock, fn($q) => $q->lockForUpdate())
            ->first();
    }

    public function create(array $data): SocialProvider
    {
        return SocialProvider::create($data);
    }
}
