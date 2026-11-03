<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'users.viewAny', 'users.create', 'users.update', 'users.delete',
        'roles.viewAny', 'roles.create', 'roles.update', 'roles.delete',
        'audit.viewAny',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect($this->permissions)
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        foreach (['Super Admin', 'Admin'] as $name) {
            $role = Role::query()->where('name', $name)->where('guard_name', 'web')->first();
            $role?->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep these permissions: they may also be used by roles configured after this migration.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
