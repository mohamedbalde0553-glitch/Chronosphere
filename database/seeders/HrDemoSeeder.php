<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Models\ShiftType;
use App\Modules\Shifts\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Départements ───────────────────────────────────────────────
        $depts = [];
        foreach ([
            ['name' => 'Production',     'code' => 'PROD'],
            ['name' => 'Ventes',         'code' => 'VENTE'],
            ['name' => 'Administration', 'code' => 'ADMIN'],
        ] as $d) {
            $depts[$d['code']] = Department::firstOrCreate(['code' => $d['code']], $d);
        }

        // ─── Postes ──────────────────────────────────────────────────────
        $positions = [];
        foreach (['Manager', 'Vendeur', 'Caissier', 'Comptable', 'Assistant', 'Opérateur', 'Technicien'] as $title) {
            $positions[$title] = Position::firstOrCreate(['title' => $title]);
        }

        // ─── Types de shifts ─────────────────────────────────────────────
        $types = [];
        foreach ([
            ['name' => 'Matin',        'start_time' => '07:00', 'end_time' => '15:00', 'color' => '#F59E0B', 'is_night' => false, 'overtime_multiplier' => 1.00],
            ['name' => 'Après-midi',   'start_time' => '15:00', 'end_time' => '23:00', 'color' => '#3B82F6', 'is_night' => false, 'overtime_multiplier' => 1.00],
            ['name' => 'Nuit',         'start_time' => '23:00', 'end_time' => '07:00', 'color' => '#6366F1', 'is_night' => true,  'overtime_multiplier' => 1.25],
            ['name' => 'Journée',      'start_time' => '09:00', 'end_time' => '17:00', 'color' => '#10B981', 'is_night' => false, 'overtime_multiplier' => 1.00],
        ] as $t) {
            $types[$t['name']] = ShiftType::firstOrCreate(['name' => $t['name']], $t);
        }

        // ─── Compétences ─────────────────────────────────────────────────
        $skills = [];
        foreach ([
            ['name' => 'Excel',          'category' => 'Informatique'],
            ['name' => 'CRM Salesforce', 'category' => 'Informatique'],
            ['name' => 'Gestion stock',  'category' => 'Logistique'],
            ['name' => 'Comptabilité',   'category' => 'Finance'],
            ['name' => 'Vente directe',  'category' => 'Commerce'],
            ['name' => 'Management',     'category' => 'RH'],
            ['name' => 'HACCP',          'category' => 'Production'],
            ['name' => 'Maintenance',    'category' => 'Technique'],
        ] as $s) {
            $skills[$s['name']] = Skill::firstOrCreate(['name' => $s['name']], $s);
        }

        // ─── Employés ────────────────────────────────────────────────────
        $employeesData = [
            ['name'=>'Alice Martin',   'dept'=>'PROD', 'pos'=>'Manager',    'code'=>'EMP-001', 'contract'=>'cdi',  'seed'=>1],
            ['name'=>'Bruno Leclerc',  'dept'=>'VENTE','pos'=>'Manager',    'code'=>'EMP-002', 'contract'=>'cdi',  'seed'=>2],
            ['name'=>'Clara Dubois',   'dept'=>'ADMIN','pos'=>'Comptable',  'code'=>'EMP-003', 'contract'=>'cdi',  'seed'=>3],
            ['name'=>'David Moreau',   'dept'=>'PROD', 'pos'=>'Opérateur',  'code'=>'EMP-004', 'contract'=>'cdi',  'seed'=>4],
            ['name'=>'Emma Bernard',   'dept'=>'VENTE','pos'=>'Vendeur',    'code'=>'EMP-005', 'contract'=>'cdd',  'seed'=>5],
            ['name'=>'Fabien Leroy',   'dept'=>'PROD', 'pos'=>'Technicien', 'code'=>'EMP-006', 'contract'=>'cdi',  'seed'=>6],
            ['name'=>'Gabrielle Simon','dept'=>'ADMIN','pos'=>'Assistant',  'code'=>'EMP-007', 'contract'=>'cdd',  'seed'=>7],
            ['name'=>'Hugo Laurent',   'dept'=>'VENTE','pos'=>'Caissier',   'code'=>'EMP-008', 'contract'=>'cdi',  'seed'=>8],
            ['name'=>'Inès Michel',    'dept'=>'PROD', 'pos'=>'Opérateur',  'code'=>'EMP-009', 'contract'=>'interim','seed'=>9],
            ['name'=>'Julien Lefebvre','dept'=>'VENTE','pos'=>'Vendeur',    'code'=>'EMP-010', 'contract'=>'cdi',  'seed'=>10],
            ['name'=>'Karine Thomas',  'dept'=>'ADMIN','pos'=>'Manager',    'code'=>'EMP-011', 'contract'=>'cdi',  'seed'=>11],
            ['name'=>'Lucas Robert',   'dept'=>'PROD', 'pos'=>'Technicien', 'code'=>'EMP-012', 'contract'=>'cdi',  'seed'=>12],
            ['name'=>'Marie Petit',    'dept'=>'VENTE','pos'=>'Vendeur',    'code'=>'EMP-013', 'contract'=>'cdd',  'seed'=>13],
            ['name'=>'Nicolas Roux',   'dept'=>'PROD', 'pos'=>'Opérateur',  'code'=>'EMP-014', 'contract'=>'cdi',  'seed'=>14],
            ['name'=>'Océane Garcia',  'dept'=>'ADMIN','pos'=>'Assistant',  'code'=>'EMP-015', 'contract'=>'freelance','seed'=>15],
        ];

        $employees = [];
        foreach ($employeesData as $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@chronosphere.local';
            $user  = User::firstOrCreate(
                ['email' => $email],
                ['name' => $data['name'], 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $user->assignRole('hr_employee');

            $emp = Employee::firstOrCreate(
                ['employee_code' => $data['code']],
                [
                    'user_id'              => $user->id,
                    'department_id'        => $depts[$data['dept']]->id,
                    'position_id'          => $positions[$data['pos']]->id,
                    'contract_type'        => $data['contract'],
                    'hire_date'            => now()->subMonths(rand(6, 36))->toDateString(),
                    'status'               => 'active',
                    'weekly_hours_minutes' => 2400,
                    'photo_url'            => "https://i.pravatar.cc/150?img={$data['seed']}",
                ]
            );

            $employees[] = $emp;
        }

        // ─── Shifts (4 prochaines semaines) ──────────────────────────────
        $typeList    = array_values($types);
        $shiftMatrix = [
            // type => [heure_début, durée_min]
            'Matin'      => ['07:00', 480],
            'Après-midi' => ['15:00', 480],
            'Nuit'       => ['23:00', 480],
            'Journée'    => ['09:00', 480],
        ];

        $startWeek = now()->startOfWeek();

        for ($week = 0; $week < 4; $week++) {
            foreach ($employees as $idx => $emp) {
                // 4 à 5 shifts par employé par semaine
                $nbShifts = rand(4, 5);
                $days     = collect(range(0, 4))->shuffle()->take($nbShifts)->sort()->values();

                foreach ($days as $dayOffset) {
                    $shiftType = $typeList[$idx % count($typeList)];
                    $matrix    = $shiftMatrix[$shiftType->name];
                    $date      = $startWeek->copy()->addWeeks($week)->addDays($dayOffset);
                    $startAt   = $date->format('Y-m-d') . ' ' . $matrix[0] . ':00';
                    $endAt     = $date->copy()->addMinutes($matrix[1])->format('Y-m-d H:i:s');

                    // Pas de doublons
                    $exists = Shift::where('employee_id', $emp->id)
                        ->where('start_at', $startAt)->exists();
                    if ($exists) continue;

                    Shift::create([
                        'employee_id'      => $emp->id,
                        'shift_type_id'    => $shiftType->id,
                        'start_at'         => $startAt,
                        'end_at'           => $endAt,
                        'worked_minutes'   => $matrix[1],
                        'overtime_minutes' => 0,
                        'status'           => 'planned',
                    ]);
                }
            }
        }

        // ─── Congés ──────────────────────────────────────────────────────
        $leaveScenarios = [
            ['emp' => 0, 'type' => 'conge_paye',  'days' => 5,  'offset_days' => 14,  'status' => 'approved'],
            ['emp' => 1, 'type' => 'rtt',          'days' => 2,  'offset_days' => 21,  'status' => 'approved'],
            ['emp' => 2, 'type' => 'maladie',      'days' => 3,  'offset_days' => 7,   'status' => 'approved'],
            ['emp' => 3, 'type' => 'conge_paye',   'days' => 7,  'offset_days' => 30,  'status' => 'pending'],
            ['emp' => 4, 'type' => 'sans_solde',   'days' => 3,  'offset_days' => 10,  'status' => 'pending'],
        ];

        foreach ($leaveScenarios as $scenario) {
            $emp       = $employees[$scenario['emp']];
            $startDate = now()->addDays($scenario['offset_days'])->toDateString();
            $endDate   = now()->addDays($scenario['offset_days'] + $scenario['days'] - 1)->toDateString();

            $leave = LeaveRequest::create([
                'employee_id' => $emp->id,
                'type'        => $scenario['type'],
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'reason'      => 'Demande générée par le seeder de démo',
                'status'      => $scenario['status'],
                'validated_at' => $scenario['status'] === 'approved' ? now() : null,
            ]);

            // Annuler les shifts si congé approuvé
            if ($scenario['status'] === 'approved') {
                Shift::where('employee_id', $emp->id)
                    ->where('start_at', '<', $endDate . ' 23:59:59')
                    ->where('end_at',   '>',  $startDate . ' 00:00:00')
                    ->update(['status' => 'cancelled']);
            }
        }

        // ─── Assignation compétences aux employés ───────────────────────
        $skillList = array_values($skills);
        foreach ($employees as $idx => $emp) {
            $empSkills = collect($skillList)->shuffle()->take(rand(2, 4));
            foreach ($empSkills as $skill) {
                if (!$emp->skills()->where('skill_id', $skill->id)->exists()) {
                    $emp->skills()->attach($skill->id, ['level' => rand(1, 5)]);
                }
            }
        }

        $this->command->info('✅ HrDemoSeeder: ' . count($employees) . ' employés, shifts 4 semaines, 5 congés, ' . count($skills) . ' compétences');
    }
}
