<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResponsableSeeder extends Seeder
{
    public function run(): void
    {
        $position = Position::firstOrCreate(
            ['title' => 'Responsable de département'],
            ['base_hourly_rate' => 30.00]
        );

        $accounts = [
            [
                'email'      => 'resp.credit@chronosphere.local',
                'name'       => 'Amara Diallo',
                'dept_code'  => 'CREDIT',
                'emp_code'   => 'RESP-001',
            ],
            [
                'email'      => 'resp.compta@chronosphere.local',
                'name'       => 'Kadiatou Balde',
                'dept_code'  => 'COMPTA',
                'emp_code'   => 'RESP-002',
            ],
            [
                'email'      => 'resp.rh@chronosphere.local',
                'name'       => 'Ousmane Sow',
                'dept_code'  => 'RH',
                'emp_code'   => 'RESP-003',
            ],
        ];

        foreach ($accounts as $data) {
            $dept = Department::where('code', $data['dept_code'])->first();
            if (!$dept) {
                $this->command->warn("Département {$data['dept_code']} introuvable, ignoré.");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'               => $data['name'],
                    'password'           => Hash::make('password'),
                    'email_verified_at'  => now(),
                ]
            );
            $user->syncRoles(['responsable']);

            $emp = Employee::firstOrCreate(
                ['employee_code' => $data['emp_code']],
                [
                    'user_id'              => $user->id,
                    'department_id'        => $dept->id,
                    'position_id'          => $position->id,
                    'contract_type'        => 'cdi',
                    'hire_date'            => '2022-01-01',
                    'status'               => 'active',
                    'weekly_hours_minutes' => 2400,
                ]
            );

            // Désigner cet employé comme manager du département
            $dept->update(['manager_id' => $emp->id]);

            $this->command->line("  ✓ responsable: {$data['email']} → {$dept->name}");
        }

        $this->command->info('✅ Comptes responsable créés.');
    }
}
