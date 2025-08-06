<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Class RolePermissionSeeder.
 *
 * @see https://spatie.be/docs/laravel-permission/v5/basic-usage/multiple-guards
 *
 * @package App\Database\Seeds
 */
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Permission List as array
        $permissions = [

            [
                'group_name' => 'dashboard',
                'permissions' => [
                    'dashboard.view_all_users_data',
                    'dashboard.filters',
                    'dashboard.show_idle_time',
                    'dashboard.show_attendance',
                    'dashboard.show_productivity',
                    'dashboard.task_list',
                    'dashboard.show_top_status'
                ]
            ],
            [
                'group_name' => 'time_entry',
                'permissions' => [
                    // Card Permissions
                    'time_entry.create',
                    'time_entry.manager_tasks',
                    'time_entry.quality_check',
                    'time_entry.agent_tasks',
                    'time_entry.view_all_tasks',
                    'time_entry.assigned_task_list',
                    'time_entry.view',
                    'time_entry.edit'
                ]
            ],
            [
                'group_name' => 'role',
                'permissions' => [
                    // role Permissions
                    'role.create',
                    'role.view',
                    'role.edit',
                    'role.delete'
                ]
            ],
            [
                'group_name' => 'profile',
                'permissions' => [
                    // profile Permissions
                    'profile.view',
                    'profile.edit',
                ]
            ],            
            [
                'group_name' => 'user',
                'permissions' => [
                    // Site Settings Permissions
                    'user.create',
                    'user.view',
                    'user.view_assigned_users',
                    'user.edit',
                    'user.delete'
                ]
            ],
            [
                'group_name' => 'workflow',
                'permissions' => [
                    'workflow.create',
                    'workflow.view',
                    'workflow.edit',
                    'workflow.delete',
                    'workflow.activate',
                    'workflow.deactivate'
                ]
            ],
            [
                'group_name' => 'client', // Added client module permissions
                'permissions' => [
                    'client.create',
                    'client.view',
                    'client.view_all_clients',
                    'client.view_assigned_clients',
                    'client.assign',
                    'client.edit',
                    'client.delete'
                ]
            ],
            [
                'group_name' => 'report', // Added Report module permissions
                'permissions' => [
                    'report.view',
                    'report.view_all_reports',
                    'report.agent_reports',
                    'report.manager_reports',
                    'report.enable_idle_report',
                    'report.qc_reports'
                ]
            ],
            [
                'group_name' => 'case_allocation', // Added Case Allocation module permissions
                'permissions' => [
                    'case_allocation.view',
                    'case_allocation.view_all_allocations',
                    'case_allocation.add',
                    'case_allocation.import_task',
                    'case_allocation.remove'
                    'case_allocation.assign_activities',
                    'case_allocation.view_assigned_activities',
                    'case_allocation.assign_task',
                    'case_allocation.view_assigned_tasks',
                ]
            ],
            [
                'group_name' => 'call_allocation', // Added Case Allocation module permissions
                'permissions' => [
                    'call_allocation.view',
                    'call_allocation.view_all_allocations',
                    'call_allocation.assign_calls',
                    'call_allocation.score_call'
                ]
            ],
            [
                'group_name' => 'ticket', // Added Ticket System permissions
                'permissions' => [
                    'ticket.view',
                    'ticket.generate',
                    'ticket.manage_ticket_form',
                    'ticket.view_assigned',
                    'ticket.view_all',
                    'ticket.reply',
                    'ticket.status'
                ]
            ],
        ];

        $role_name = 'superadmin';
        // Do same for the admin guard for tutorial purposes
        // $roleSuperAdmin = Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
         $roleSuperAdmin = Role::firstOrCreate(
            ['name' => $role_name, 'guard_name' => 'web'],
            ['name' => $role_name, 'guard_name' => 'web']
        );

        // Create and Assign Permissions
        for ($i = 0; $i < count($permissions); $i++) {
            $permissionGroup = $permissions[$i]['group_name'];
            for ($j = 0; $j < count($permissions[$i]['permissions']); $j++) {
                // Create Permission
                $permission = Permission::firstOrCreate(['name' => $permissions[$i]['permissions'][$j], 'group_name' => $permissionGroup, 'guard_name' => 'web']);
                $roleSuperAdmin->givePermissionTo($permission);
                $permission->assignRole($roleSuperAdmin);
            }
        }

        // Assign super admin role permission to superadmin user
        $user = User::where('email', 'superadmin@mailinator.com')->first();
        if ($user) {
            $user->assignRole($roleSuperAdmin);
        }
    }
}
