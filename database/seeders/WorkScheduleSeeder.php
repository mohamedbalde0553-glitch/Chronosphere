<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\WorkSchedule;
use App\Modules\Shifts\Models\WorkScheduleDay;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@chronosphere.local')->first();
        if (!$admin) {
            $this->command->warn('Admin user introuvable, skip.');
            return;
        }

        $schedules = [
            [
                'name'        => 'Horaire Standard (Lun–Ven)',
                'description' => '9h–17h30 du lundi au vendredi, pause 30 min',
                'dept_code'   => 'DG',
                'color'       => '#10B981',
                'days' => [
                    ['day' => 1, 'start' => '09:00', 'end' => '17:30', 'break' => 30],
                    ['day' => 2, 'start' => '09:00', 'end' => '17:30', 'break' => 30],
                    ['day' => 3, 'start' => '09:00', 'end' => '17:30', 'break' => 30],
                    ['day' => 4, 'start' => '09:00', 'end' => '17:30', 'break' => 30],
                    ['day' => 5, 'start' => '09:00', 'end' => '17:30', 'break' => 30],
                ],
            ],
            [
                'name'        => 'Horaire Crédits (Lun–Sam)',
                'description' => '8h–16h du lundi au samedi, pause 45 min',
                'dept_code'   => 'CREDIT',
                'color'       => '#3B82F6',
                'days' => [
                    ['day' => 1, 'start' => '08:00', 'end' => '16:00', 'break' => 45],
                    ['day' => 2, 'start' => '08:00', 'end' => '16:00', 'break' => 45],
                    ['day' => 3, 'start' => '08:00', 'end' => '16:00', 'break' => 45],
                    ['day' => 4, 'start' => '08:00', 'end' => '16:00', 'break' => 45],
                    ['day' => 5, 'start' => '08:00', 'end' => '16:00', 'break' => 45],
                    ['day' => 6, 'start' => '08:00', 'end' => '13:00', 'break' => 0, 'multiplier' => 1.25],
                ],
            ],
            [
                'name'        => 'Horaire Comptabilité',
                'description' => '8h30–17h du lundi au vendredi, pause 30 min',
                'dept_code'   => 'COMPTA',
                'color'       => '#F59E0B',
                'days' => [
                    ['day' => 1, 'start' => '08:30', 'end' => '17:00', 'break' => 30],
                    ['day' => 2, 'start' => '08:30', 'end' => '17:00', 'break' => 30],
                    ['day' => 3, 'start' => '08:30', 'end' => '17:00', 'break' => 30],
                    ['day' => 4, 'start' => '08:30', 'end' => '17:00', 'break' => 30],
                    ['day' => 5, 'start' => '08:30', 'end' => '17:00', 'break' => 30],
                ],
            ],
            [
                'name'        => 'Horaire RH & IT',
                'description' => '8h–16h30 du lundi au vendredi, pause 30 min',
                'dept_code'   => 'RH',
                'color'       => '#8B5CF6',
                'days' => [
                    ['day' => 1, 'start' => '08:00', 'end' => '16:30', 'break' => 30],
                    ['day' => 2, 'start' => '08:00', 'end' => '16:30', 'break' => 30],
                    ['day' => 3, 'start' => '08:00', 'end' => '16:30', 'break' => 30],
                    ['day' => 4, 'start' => '08:00', 'end' => '16:30', 'break' => 30],
                    ['day' => 5, 'start' => '08:00', 'end' => '16:30', 'break' => 30],
                ],
            ],
            [
                'name'        => 'Horaire Commercial (Lun–Sam)',
                'description' => '7h30–15h30 du lundi au samedi, pause 30 min',
                'dept_code'   => 'COMM',
                'color'       => '#EF4444',
                'days' => [
                    ['day' => 1, 'start' => '07:30', 'end' => '15:30', 'break' => 30],
                    ['day' => 2, 'start' => '07:30', 'end' => '15:30', 'break' => 30],
                    ['day' => 3, 'start' => '07:30', 'end' => '15:30', 'break' => 30],
                    ['day' => 4, 'start' => '07:30', 'end' => '15:30', 'break' => 30],
                    ['day' => 5, 'start' => '07:30', 'end' => '15:30', 'break' => 30],
                    ['day' => 6, 'start' => '07:30', 'end' => '12:00', 'break' => 0, 'multiplier' => 1.25],
                ],
            ],
        ];

        $today = now()->toDateString();
        $end   = now()->addYear()->toDateString();

        foreach ($schedules as $data) {
            $dept = Department::where('code', $data['dept_code'])->first();

            $schedule = WorkSchedule::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description'   => $data['description'],
                    'start_date'    => $today,
                    'end_date'      => $end,
                    'department_id' => $dept?->id,
                    'created_by'    => $admin->id,
                    'color'         => $data['color'],
                    'is_active'     => true,
                ]
            );

            // Recréer les jours si nouveaux
            if ($schedule->wasRecentlyCreated) {
                foreach ($data['days'] as $day) {
                    WorkScheduleDay::create([
                        'work_schedule_id'     => $schedule->id,
                        'day_of_week'          => $day['day'],
                        'start_time'           => $day['start'],
                        'end_time'             => $day['end'],
                        'break_minutes'        => $day['break'],
                        'is_overtime_eligible' => true,
                        'multiplier'           => $day['multiplier'] ?? 1.0,
                    ]);
                }
                $this->command->line("  ✓ {$data['name']} ({$data['dept_code']})");
            } else {
                $this->command->line("  ~ {$data['name']} (déjà existant)");
            }
        }

        $this->command->info('✅ Horaires périodiques créés.');
    }
}
