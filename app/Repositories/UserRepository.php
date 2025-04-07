<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function createUser(array $userData)
    {
        return User::create($userData);
    }
}
