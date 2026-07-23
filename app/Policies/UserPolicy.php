<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Establishes the ownership convention every future customer-owned
     * resource policy follows: authorize against the model instance, never
     * a client-supplied ID.
     */
    public function view(User $user, User $model): bool
    {
        return $user->is($model);
    }

    public function update(User $user, User $model): bool
    {
        return $user->is($model);
    }
}
