<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Module Universitaire
            'timetable.view',
            'timetable.create',
            'timetable.edit',
            'timetable.delete',
            'timetable.export',
            'timetable.manage_rooms',
            'timetable.manage_teachers',

            // Module Employés / Quarts
            'shifts.view',
            'shifts.create',
            'shifts.edit',
            'shifts.delete',
            'shifts.validate_leave',
            'shifts.manage_employees',
            'shifts.export',

            // Module Agenda
            'calendar.view',
            'calendar.create',
            'calendar.edit',
            'calendar.delete',
            'calendar.share',
            'calendar.manage_all',

            // Module Réservation
            'booking.view',
            'booking.create',
            'booking.edit',
            'booking.delete',
            'booking.approve',
            'booking.manage_resources',

            // Module Projet
            'project.view',
            'project.create',
            'project.edit',
            'project.delete',
            'project.manage_team',
            'project.manage_all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
