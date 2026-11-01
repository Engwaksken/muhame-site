<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $postPermissions = ['posts.viewAny', 'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish'];
        $taxonomyPermissions = collect(['categories', 'tags'])
            ->flatMap(fn (string $resource) => array_map(
                fn (string $action) => "{$resource}.{$action}",
                ['viewAny', 'create', 'update', 'delete'],
            ))
            ->all();
        $allPermissions = array_merge($postPermissions, $taxonomyPermissions, ['settings.viewAny', 'settings.update']);

        collect($allPermissions)
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        SiteSetting::current();

        $roles = [
            'Super Admin' => $allPermissions,
            'Admin' => $allPermissions,
            'Editor' => array_merge($postPermissions, ['categories.viewAny', 'categories.create', 'categories.update', 'tags.viewAny', 'tags.create', 'tags.update']),
            'Author' => ['posts.viewAny', 'posts.view', 'posts.create', 'posts.update'],
            'Contributor' => ['posts.viewAny', 'posts.view', 'posts.create'],
            'Moderator' => ['posts.viewAny', 'posts.view', 'posts.update', 'posts.publish', 'categories.viewAny', 'tags.viewAny'],
            'Viewer' => ['posts.viewAny', 'posts.view'],
        ];

        foreach ($roles as $name => $grantedPermissions) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($grantedPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
