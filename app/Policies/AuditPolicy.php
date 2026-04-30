<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Audit;
use App\Models\User;

class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::OWNER]);
    }

    public function view(User $user, Audit $audit): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::OWNER]);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Audit $audit): bool
    {
        return false;
    }

    public function delete(User $user, Audit $audit): bool
    {
        return false;
    }

    public function restore(User $user, Audit $audit): bool
    {
        return false;
    }

    public function forceDelete(User $user, Audit $audit): bool
    {
        return false;
    }
}
