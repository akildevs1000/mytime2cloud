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

        Permission::create(['module' => 'department', 'title' => 'department create', 'name' => 'department_create']);
        Permission::create(['module' => 'department', 'title' => 'department edit', 'name' => 'department_edit']);
        Permission::create(['module' => 'department', 'title' => 'department delete', 'name' => 'department_delete']);
        Permission::create(['module' => 'department', 'title' => 'department view', 'name' => 'department_view']);
        Permission::create(['module' => 'department', 'title' => 'department access', 'name' => 'department_access']);

        Permission::create(['module' => 'sub_department', 'title' => 'sub department create', 'name' => 'sub_department_create']);
        Permission::create(['module' => 'sub_department', 'title' => 'sub department edit', 'name' => 'sub_department_edit']);
        Permission::create(['module' => 'sub_department', 'title' => 'sub department delete', 'name' => 'sub_department_delete']);
        Permission::create(['module' => 'sub_department', 'title' => 'sub department view', 'name' => 'sub_department_view']);
        Permission::create(['module' => 'sub_department', 'title' => 'sub department access', 'name' => 'sub_department_access']);

        Permission::create(['module' => 'designation', 'title' => 'designation create', 'name' => 'designation_create']);
        Permission::create(['module' => 'designation', 'title' => 'designation edit', 'name' => 'designation_edit']);
        Permission::create(['module' => 'designation', 'title' => 'designation delete', 'name' => 'designation_delete']);
        Permission::create(['module' => 'designation', 'title' => 'designation view', 'name' => 'designation_view']);
        Permission::create(['module' => 'designation', 'title' => 'designation access', 'name' => 'designation_access']);

        Permission::create(['module' => 'role', 'title' => 'role create', 'name' => 'role_create']);
        Permission::create(['module' => 'role', 'title' => 'role edit', 'name' => 'role_edit']);
        Permission::create(['module' => 'role', 'title' => 'role delete', 'name' => 'role_delete']);
        Permission::create(['module' => 'role', 'title' => 'role view', 'name' => 'role_view']);
        Permission::create(['module' => 'role', 'title' => 'role access', 'name' => 'role_access']);

        Permission::create(['module' => 'assign_permission', 'title' => 'assign permission create', 'name' => 'assign_permission_create']);
        Permission::create(['module' => 'assign_permission', 'title' => 'assign permission edit', 'name' => 'assign_permission_edit']);
        Permission::create(['module' => 'assign_permission', 'title' => 'assign permission delete', 'name' => 'assign_permission_delete']);
        Permission::create(['module' => 'assign_permission', 'title' => 'assign permission view', 'name' => 'assign_permission_view']);
        Permission::create(['module' => 'assign_permission', 'title' => 'assign permission access', 'name' => 'assign_permission_access']);

        Permission::create(['module' => 'employee', 'title' => 'employee create', 'name' => 'employee_create']);
        Permission::create(['module' => 'employee', 'title' => 'employee edit', 'name' => 'employee_edit']);
        Permission::create(['module' => 'employee', 'title' => 'employee delete', 'name' => 'employee_delete']);
        Permission::create(['module' => 'employee', 'title' => 'employee view', 'name' => 'employee_view']);
        Permission::create(['module' => 'employee', 'title' => 'employee access', 'name' => 'employee_access']);

        Permission::create(['module' => 'announcement', 'title' => 'announcement create', 'name' => 'announcement_create']);
        Permission::create(['module' => 'announcement', 'title' => 'announcement edit', 'name' => 'announcement_edit']);
        Permission::create(['module' => 'announcement', 'title' => 'announcement delete', 'name' => 'announcement_delete']);
        Permission::create(['module' => 'announcement', 'title' => 'announcement view', 'name' => 'announcement_view']);
        Permission::create(['module' => 'announcement', 'title' => 'announcement access', 'name' => 'announcement_access']);

        Permission::create(['module' => 'policy', 'title' => 'policy create', 'name' => 'policy_create']);
        Permission::create(['module' => 'policy', 'title' => 'policy edit', 'name' => 'policy_edit']);
        Permission::create(['module' => 'policy', 'title' => 'policy delete', 'name' => 'policy_delete']);
        Permission::create(['module' => 'policy', 'title' => 'policy view', 'name' => 'policy_view']);
        Permission::create(['module' => 'policy', 'title' => 'policy access', 'name' => 'policy_access']);
    }
}
