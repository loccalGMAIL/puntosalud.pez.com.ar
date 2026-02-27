<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessModule('system');
    }

    public function create(User $user): bool
    {
        return $user->canAccessModule('system');
    }

    public function update(User $user, Profile $profile): bool
    {
        return $user->canAccessModule('system');
    }

    public function delete(User $user, Profile $profile): bool
    {
        return $user->canAccessModule('system');
    }
}
