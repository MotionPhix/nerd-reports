<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Project Management
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'manage project members',

            // Task Management
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'assign tasks',
            'complete tasks',

            // Contact Management
            'view contacts',
            'create contacts',
            'edit contacts',
            'delete contacts',

            // Firm Management
            'view firms',
            'create firms',
            'edit firms',
            'delete firms',

            // Interaction Management
            'view interactions',
            'create interactions',
            'edit interactions',
            'delete interactions',

            // Report Management
            'view reports',
            'create reports',
            'edit reports',
            'delete reports',
            'send reports',
            'manage report templates',

            // Board Management
            'view boards',
            'create boards',
            'edit boards',
            'delete boards',

            // System Administration
            'manage roles',
            'manage permissions',
            'view system logs',
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - has most permissions except system administration
        $adminRole = Role::create(['name' => 'admin']);
        $adminPermissions = Permission::whereNotIn('name', [
            'manage roles',
            'manage permissions',
            'view system logs',
            'manage system settings',
        ])->get();
        $adminRole->givePermissionTo($adminPermissions);

        // Project Manager - can manage projects, tasks, and reports
        $projectManagerRole = Role::create(['name' => 'project-manager']);
        $projectManagerPermissions = [
            'view projects', 'create projects', 'edit projects', 'manage project members',
            'view tasks', 'create tasks', 'edit tasks', 'assign tasks', 'complete tasks',
            'view boards', 'create boards', 'edit boards',
            'view contacts', 'create contacts', 'edit contacts',
            'view firms', 'create firms', 'edit firms',
            'view interactions', 'create interactions', 'edit interactions',
            'view reports', 'create reports', 'send reports',
        ];
        $projectManagerRole->givePermissionTo($projectManagerPermissions);

        // Team Member - basic permissions for assigned work
        $teamMemberRole = Role::create(['name' => 'team-member']);
        $teamMemberPermissions = [
            'view projects',
            'view tasks', 'edit tasks', 'complete tasks',
            'view boards',
            'view contacts',
            'view firms',
            'view interactions', 'create interactions',
            'view reports',
        ];
        $teamMemberRole->givePermissionTo($teamMemberPermissions);

        // Client - limited view permissions
        $clientRole = Role::create(['name' => 'client']);
        $clientPermissions = [
            'view projects',
            'view tasks',
            'view reports',
        ];
        $clientRole->givePermissionTo($clientPermissions);

        // Create a super admin user if it doesn't exist
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@nerdreports.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdminUser->assignRole($superAdminRole);

        $this->command->info('Roles and permissions have been seeded successfully!');
        $this->command->info('Super Admin User: admin@nerdreports.com / password');
    }
}
