<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions
        $permissions = [
            'roles' => ['create', 'read', 'update', 'delete', 'view'],
            'permissions' => ['create', 'read', 'update', 'delete', 'view'],
            'users' => ['create', 'read', 'update', 'delete', 'view'],
            'specialists' => ['create', 'read', 'update', 'delete', 'view'],
            'companies' => ['create', 'read', 'update', 'delete', 'view'],
            'admins' => ['create', 'read', 'update', 'delete', 'view'],
            'banners' => ['create', 'read', 'update', 'delete', 'view'],
            'settings' => ['read', 'update'],
            'contacts' => ['read', 'update'],
            'static_pages' => ['read', 'update'],
            'logs' => ['create', 'read', 'update', 'delete', 'view'],
            'notifications' => ['read', 'update', 'create', 'delete'],
            'advantages' => ['read', 'update', 'create', 'delete'],
            'reviews' => ['read', 'update'],
        ];

        foreach ($permissions as $group => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    ['name' => "$action $group"],
                    ['name' => "$action $group"]
                );
            }
        }

        // Roles
        $roles = [
            'superadmin' => Permission::pluck('name')->toArray(),
            'admin' => [
                'create roles', 'read roles', 'view roles', 'update roles',
                'create permissions', 'read permissions', 'view permissions',
                'create users', 'read users', 'view users', 'update users',
                'create logs', 'read logs', 'view logs', 'update logs',
            ],
            'staff' => [],
            'user' => [],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::updateOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions); // Assign permissions
        }

        // Users
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => '12345678',
                'role' => 'superadmin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => '12345678',
                'role' => 'admin',
            ],
            // [
            //     'name' => 'Staff',
            //     'email' => 'staff@gmail.com',
            //     'password' => '12345678',
            //     'role' => 'staff',
            // ],
        ];

        foreach ($users as $userData) {

            // $user = User::where('email', $userData['email'])->first();
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                ]
            );
            $user->assignRole($userData['role']);
        }
    }
}
