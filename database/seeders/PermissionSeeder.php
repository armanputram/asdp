<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\File;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil semua resource yang ada di folder Filament Resources
        $resourcePath = app_path('Filament/Resources');
        $resources = collect(File::files($resourcePath))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), 'Resource.php'))
            ->map(fn($file) => Str::replaceLast('Resource.php', '', $file->getFilename()))
            ->values();

        $permissions = [];

        // 2. Generate permission dasar untuk tiap resource
        foreach ($resources as $resource) {
            $name = Str::kebab($resource); // ex: User → user

            foreach (['view', 'view_any', 'create', 'update', 'delete'] as $action) {
                $permissions[] = "{$action}_{$name}";
            }
        }

        // 3. Simpan permission ke database
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 4. Buat Role Super Admin
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // 5. Assign semua permission ke Super Admin
        $role->syncPermissions(Permission::all());

        // 6. Buat user admin
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('1'), // Default password
            ]
        );

        $user->assignRole($role);

        $this->command->info('✅ Permission, role, and admin user successfully created.');
    }
}
