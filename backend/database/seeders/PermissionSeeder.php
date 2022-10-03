<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Permission::create(['name'=>'company_create']);
        // Permission::create(['name'=>'company_edit']);
        // Permission::create(['name'=>'company_delete']);
        // Permission::create(['name'=>'company_view']);
        // Permission::create(['name'=>'company_access']);

        // Permission::create(['name'=>'branch_create']);
        // Permission::create(['name'=>'branch_edit']);
        // Permission::create(['name'=>'branch_delete']);
        // Permission::create(['name'=>'branch_view']);
        // Permission::create(['name'=>'branch_access']);

        // Permission::create(['name'=>'device_create']);
        // Permission::create(['name'=>'device_edit']);
        // Permission::create(['name'=>'device_delete']);
        // Permission::create(['name'=>'device_view']);
        // Permission::create(['name'=>'device_access']);

        // Permission::create(['name'=>'module_create']);
        // Permission::create(['name'=>'module_edit']);
        // Permission::create(['name'=>'module_delete']);
        // Permission::create(['name'=>'module_view']);
        // Permission::create(['name'=>'module_access']);

        // Permission::create(['name'=>'assign_module_create']);
        // Permission::create(['name'=>'assign_module_edit']);
        // Permission::create(['name'=>'assign_module_delete']);
        // Permission::create(['name'=>'assign_module_view']);
        // Permission::create(['name'=>'assign_module_access']);

        // Permission::create(['name'=>'user_create']);
        // Permission::create(['name'=>'user_edit']);
        // Permission::create(['name'=>'user_delete']);
        // Permission::create(['name'=>'user_view']);
        // Permission::create(['name'=>'user_access']);

        // Permission::create(['name'=>'role_create']);
        // Permission::create(['name'=>'role_edit']);
        // Permission::create(['name'=>'role_delete']);
        // Permission::create(['name'=>'role_view']);
        // Permission::create(['name'=>'role_access']);

        // Permission::create(['name'=>'assign_permission_create']);
        // Permission::create(['name'=>'assign_permission_edit']);
        // Permission::create(['name'=>'assign_permission_delete']);
        // Permission::create(['name'=>'assign_permission_view']);
        // Permission::create(['name'=>'assign_permission_access']);

        Permission::create(['name'=>'department_create']);
        Permission::create(['name'=>'department_edit']);
        Permission::create(['name'=>'department_delete']);
        Permission::create(['name'=>'department_view']);
        Permission::create(['name'=>'department_access']);

        Permission::create(['name'=>'sub_department_create']);
        Permission::create(['name'=>'sub_department_edit']);
        Permission::create(['name'=>'sub_department_delete']);
        Permission::create(['name'=>'sub_department_view']);
        Permission::create(['name'=>'sub_department_access']);

        Permission::create(['name'=>'designation_create']);
        Permission::create(['name'=>'designation_edit']);
        Permission::create(['name'=>'designation_delete']);
        Permission::create(['name'=>'designation_view']);
        Permission::create(['name'=>'designation_access']);

        Permission::create(['name'=>'role_create']);
        Permission::create(['name'=>'role_edit']);
        Permission::create(['name'=>'role_delete']);
        Permission::create(['name'=>'role_view']);
        Permission::create(['name'=>'role_access']);

        Permission::create(['name'=>'assign_permission_create']);
        Permission::create(['name'=>'assign_permission_edit']);
        Permission::create(['name'=>'assign_permission_delete']);
        Permission::create(['name'=>'assign_permission_view']);
        Permission::create(['name'=>'assign_permission_access']);

        Permission::create(['name'=>'employee_create']);
        Permission::create(['name'=>'employee_edit']);
        Permission::create(['name'=>'employee_delete']);
        Permission::create(['name'=>'employee_view']);
        Permission::create(['name'=>'employee_access']);

        Permission::create(['name'=>'announcement_create']);
        Permission::create(['name'=>'announcement_edit']);
        Permission::create(['name'=>'announcement_delete']);
        Permission::create(['name'=>'announcement_view']);
        Permission::create(['name'=>'announcement_access']);

        Permission::create(['name'=>'policy_create']);
        Permission::create(['name'=>'policy_edit']);
        Permission::create(['name'=>'policy_delete']);
        Permission::create(['name'=>'policy_view']);
        Permission::create(['name'=>'policy_access']);
    }
}
