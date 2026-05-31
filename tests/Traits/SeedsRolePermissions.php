<?php

namespace Tests\Traits;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait SeedsRolePermissions
{
    /**
     * Crée les permissions et le rôle demandé, assigne le rôle à un User existant.
     */
    protected function seedRole(string $roleName, array $permissions, ?\App\Models\User $user = null): \Spatie\Permission\Models\Role
    {
        $permModels = array_map(
            fn($p) => Permission::firstOrCreate(['name' => $p]),
            $permissions
        );

        $role = Role::firstOrCreate(['name' => $roleName]);
        $role->syncPermissions($permModels);

        if ($user) {
            $user->assignRole($roleName);
        }

        return $role;
    }
}
