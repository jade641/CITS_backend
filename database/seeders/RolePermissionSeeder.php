<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\AuthorizationMatrix;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AuthorizationMatrix::permissions() as $permissionData) {
            Permission::query()->updateOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData,
            );
        }

        foreach (AuthorizationMatrix::roles() as $roleData) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData,
            );

            $permissionIds = Permission::query()
                ->whereIn('slug', AuthorizationMatrix::rolePermissions()[$roleData['slug']])
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
