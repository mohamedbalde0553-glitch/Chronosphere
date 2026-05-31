<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'super_admin' => Permission::all()->pluck('name')->toArray(),

            'uni_admin' => [
                'timetable.view', 'timetable.create', 'timetable.edit',
                'timetable.delete', 'timetable.export',
                'timetable.manage_rooms', 'timetable.manage_teachers',
            ],
            'uni_teacher' => [
                'timetable.view', 'timetable.create', 'timetable.edit',
            ],
            'uni_student' => [
                'timetable.view',
            ],

            'hr_manager' => [
                'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete',
                'shifts.validate_leave', 'shifts.manage_employees', 'shifts.export',
            ],
            'responsable' => [
                'shifts.view', 'shifts.create', 'shifts.edit',
                'shifts.validate_leave', 'shifts.manage_department',
            ],
            'hr_employee' => [
                'shifts.view',
            ],

            'cal_user' => [
                'calendar.view', 'calendar.create', 'calendar.edit',
                'calendar.delete', 'calendar.share',
            ],

            'book_manager' => [
                'booking.view', 'booking.create', 'booking.edit', 'booking.delete',
                'booking.approve', 'booking.manage_resources',
            ],
            'book_user' => [
                'booking.view', 'booking.create',
            ],

            'proj_manager' => [
                'project.view', 'project.create', 'project.edit', 'project.delete',
                'project.manage_team', 'project.manage_all',
            ],
            'proj_member' => [
                'project.view', 'project.create', 'project.edit',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }
    }
}
