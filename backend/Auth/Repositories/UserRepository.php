<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function findByConditions(array $conditions = [], bool $lock = false): ?User
    {
        return User
            ::when($lock, fn($q) => $q->lockForUpdate())
            ->where($conditions)
            ->first();
    }

    public function getByConditions(array $conditions = [], bool $lock = false): Collection
    {
        return User
            ::when($lock, fn($q) => $q->lockForUpdate())
            ->where($conditions)
            ->get();
    }

    public function create(array $data = [])
    {
        return User::create($data);
    }

    public function updateById(int $id, array $data = []): bool
    {
        return User::where(['id' => $id])->update($data);
    }
}
