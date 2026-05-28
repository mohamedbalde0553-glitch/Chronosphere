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
use App\Modules\Shifts\Models\WorkSchedule;
use App\Modules\Shifts\Models\WorkScheduleDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MicrofinanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏦 Seeding microfinance data...');
        $password = Hash::make('password');

        // ── Departments ──────────────────────────────────────────────────
        $deptData = [
            ['name' => 'Direction Générale',       'code' => 'DG'],
            ['name' => 'Direction des Crédits',    'code' => 'CREDIT'],
            ['name' => 'Épargne & Dépôts',         'code' => 'EPARGNE'],
            ['name' => 'Comptabilité & Finance',   'code' => 'COMPTA'],
            ['name' => 'Ressources Humaines',      'code' => 'RH'],
            ['name' => 'Informatique & Systèmes',  'code' => 'IT'],
            ['name' => 'Commercial & Marketing',   'code' => 'COMM'],
            ['name' => 'Recouvrement',             'code' => 'RECOUVR'],
            ['name' => 'Audit & Contrôle Interne', 'code' => 'AUDIT'],
            ['name' => 'Juridique & Conformité',   'code' => 'JURIDIQUE'],
        ];
        $depts = [];
        foreach ($deptData as $d) {
            $depts[$d['code']] = Department::firstOrCreate(['code' => $d['code']], ['name' => $d['name'], 'code' => $d['code']]);
        }
        $this->command->info('  ✓ ' . count($depts) . ' departments');

        // ── Positions ────────────────────────────────────────────────────
        $posData = [
            ['title' => 'Directeur Général',           'base_hourly_rate' => 45.00],
            ['title' => 'Directeur de Crédit',         'base_hourly_rate' => 35.00],
            ['title' => 'Directeur Financier',         'base_hourly_rate' => 35.00],
            ['title' => 'Responsable RH',              'base_hourly_rate' => 28.00],
            ['title' => 'Responsable IT',              'base_hourly_rate' => 30.00],
            ['title' => 'Chef de Département',         'base_hourly_rate' => 25.00],
            ['title' => 'Analyste Crédit',             'base_hourly_rate' => 20.00],
            ['title' => 'Agent de Crédit Senior',      'base_hourly_rate' => 18.00],
            ['title' => 'Agent de Crédit',             'base_hourly_rate' => 14.00],
            ['title' => 'Caissier Principal',          'base_hourly_rate' => 16.00],
            ['title' => 'Caissier',                    'base_hourly_rate' => 12.00],
            ['title' => 'Comptable Senior',            'base_hourly_rate' => 20.00],
            ['title' => 'Comptable',                   'base_hourly_rate' => 15.00],
            ['title' => 'Chargé de Clientèle',         'base_hourly_rate' => 14.00],
            ['title' => 'Agent Commercial',            'base_hourly_rate' => 12.00],
            ['title' => 'Agent de Recouvrement',       'base_hourly_rate' => 13.00],
            ['title' => 'Auditeur Interne',            'base_hourly_rate' => 22.00],
            ['title' => 'Juriste',                     'base_hourly_rate' => 24.00],
            ['title' => 'Développeur',                 'base_hourly_rate' => 22.00],
            ['title' => 'Assistant Administratif',     'base_hourly_rate' => 10.00],
            ['title' => 'Agent de Sécurité',           'base_hourly_rate' => 9.00],
            ['title' => 'Stagiaire',                   'base_hourly_rate' => 5.00],
        ];
        $positions = [];
        foreach ($posData as $p) {
            $positions[$p['title']] = Position::firstOrCreate(['title' => $p['title']], $p);
        }
        $this->command->info('  ✓ ' . count($positions) . ' positions');

        // ── Shift Types ──────────────────────────────────────────────────
        $shiftTypes = [];
        foreach ([
            ['name' => 'Journée',    'start_time' => '08:00', 'end_time' => '16:30', 'color' => '#10B981', 'is_night' => false, 'overtime_multiplier' => 1.00],
            ['name' => 'Matin',      'start_time' => '07:00', 'end_time' => '13:00', 'color' => '#F59E0B', 'is_night' => false, 'overtime_multiplier' => 1.00],
            ['name' => 'Guichet',    'start_time' => '08:00', 'end_time' => '17:00', 'color' => '#3B82F6', 'is_night' => false, 'overtime_multiplier' => 1.00],
            ['name' => 'Nuit',       'start_time' => '22:00', 'end_time' => '06:00', 'color' => '#6366F1', 'is_night' => true,  'overtime_multiplier' => 1.50],
        ] as $t) {
            $shiftTypes[$t['name']] = ShiftType::firstOrCreate(['name' => $t['name']], $t);
        }

        // ── Skills ───────────────────────────────────────────────────────
        $skillData = [
            ['name' => 'Analyse de crédit',   'category' => 'Finance'],
            ['name' => 'Gestion des risques', 'category' => 'Finance'],
            ['name' => 'Excel avancé',        'category' => 'Informatique'],
            ['name' => 'Core Banking (CBS)',   'category' => 'Informatique'],
            ['name' => 'Conformité AML/KYC',  'category' => 'Juridique'],
            ['name' => 'Accueil clientèle',   'category' => 'Commercial'],
            ['name' => 'Recouvrement amiable','category' => 'Juridique'],
            ['name' => 'Comptabilité SYSCOA', 'category' => 'Finance'],
            ['name' => 'Développement mobile','category' => 'IT'],
            ['name' => 'Management d\'équipe','category' => 'RH'],
        ];
        $skills = [];
        foreach ($skillData as $s) {
            $skills[] = Skill::firstOrCreate(['name' => $s['name']], $s);
        }
        $this->command->info('  ✓ ' . count($skills) . ' skills');

        // ── Work Schedules ───────────────────────────────────────────────
        $adminUser = User::first();
        $scheduleOffice = WorkSchedule::firstOrCreate(['name' => 'Horaire Bureau Standard'], [
            'name'          => 'Horaire Bureau Standard',
            'description'   => 'Lun-Ven 8h-16h30, pause 1h — tous les services administratifs',
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'department_id' => $depts['COMPTA']->id,
            'created_by'    => $adminUser->id,
            'is_active'     => true,
        ]);
        foreach ([1,2,3,4,5] as $dow) {
            WorkScheduleDay::firstOrCreate(
                ['work_schedule_id' => $scheduleOffice->id, 'day_of_week' => $dow],
                ['start_time' => '08:00', 'end_time' => '16:30', 'break_minutes' => 60, 'is_overtime_eligible' => true, 'multiplier' => 1.0]
            );
        }

        $scheduleCredit = WorkSchedule::firstOrCreate(['name' => 'Horaire Crédit & Commercial'], [
            'name'          => 'Horaire Crédit & Commercial',
            'description'   => 'Lun-Sam 8h-17h — agents terrain et guichet',
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'department_id' => $depts['CREDIT']->id,
            'created_by'    => $adminUser->id,
            'is_active'     => true,
        ]);
        foreach ([1,2,3,4,5,6] as $dow) {
            WorkScheduleDay::firstOrCreate(
                ['work_schedule_id' => $scheduleCredit->id, 'day_of_week' => $dow],
                ['start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60, 'is_overtime_eligible' => true, 'multiplier' => $dow === 6 ? 1.25 : 1.0]
            );
        }

        $scheduleRecouvr = WorkSchedule::firstOrCreate(['name' => 'Horaire Recouvrement'], [
            'name'          => 'Horaire Recouvrement',
            'description'   => 'Lun-Ven 9h-18h — équipe terrain recouvrement',
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'department_id' => $depts['RECOUVR']->id,
            'created_by'    => $adminUser->id,
            'is_active'     => true,
        ]);
        foreach ([1,2,3,4,5] as $dow) {
            WorkScheduleDay::firstOrCreate(
                ['work_schedule_id' => $scheduleRecouvr->id, 'day_of_week' => $dow],
                ['start_time' => '09:00', 'end_time' => '18:00', 'break_minutes' => 60, 'is_overtime_eligible' => true, 'multiplier' => 1.0]
            );
        }

        $this->command->info('  ✓ 3 work schedules');

        // ── Employees distribution per department ────────────────────────
        $deptEmployeeDist = [
            'DG'       => ['count' => 5,  'positions' => ['Directeur Général', 'Chef de Département', 'Assistant Administratif']],
            'CREDIT'   => ['count' => 45, 'positions' => ['Directeur de Crédit', 'Analyste Crédit', 'Agent de Crédit Senior', 'Agent de Crédit', 'Stagiaire']],
            'EPARGNE'  => ['count' => 25, 'positions' => ['Chef de Département', 'Caissier Principal', 'Caissier', 'Chargé de Clientèle']],
            'COMPTA'   => ['count' => 15, 'positions' => ['Directeur Financier', 'Comptable Senior', 'Comptable', 'Assistant Administratif']],
            'RH'       => ['count' => 8,  'positions' => ['Responsable RH', 'Assistant Administratif', 'Stagiaire']],
            'IT'       => ['count' => 10, 'positions' => ['Responsable IT', 'Développeur', 'Assistant Administratif']],
            'COMM'     => ['count' => 20, 'positions' => ['Chef de Département', 'Agent Commercial', 'Chargé de Clientèle']],
            'RECOUVR'  => ['count' => 18, 'positions' => ['Chef de Département', 'Agent de Recouvrement', 'Juriste']],
            'AUDIT'    => ['count' => 6,  'positions' => ['Chef de Département', 'Auditeur Interne']],
            'JURIDIQUE'=> ['count' => 5,  'positions' => ['Chef de Département', 'Juriste', 'Assistant Administratif']],
        ];

        $firstNames = ['Amadou','Fatou','Ibrahima','Mariama','Cheikh','Aissatou','Mamadou','Rokhaya',
                       'Ousmane','Ndéye','Abdoulaye','Bineta','Moussa','Coumba','Modou','Khady',
                       'Seydou','Adja','Lamine','Yaye','Omar','Mame','Pape','Astou','Serigne',
                       'Dieynaba','Alioune','Sokhna','El Hadji','Rama','Babacar','Dior','Thierno',
                       'Ndeye','Malick','Soda','Idrissa','Awa','Assane','Daba','Mbaye','Amy',
                       'Djibril','Khadija','Boubacar','Yacine','Souleymane','Dioulde','Bocar','Nafi'];

        $lastNames = ['Diallo','Sow','Ndiaye','Ba','Diop','Fall','Sarr','Mbaye','Thiam','Gueye',
                      'Camara','Kouyaté','Baldé','Barry','Bah','Cissé','Kanté','Konaté','Traoré',
                      'Coulibaly','Dembélé','Keita','Sissoko','Fofana','Diabaté','Sylla','Touré'];

        $contractTypes = ['cdi','cdi','cdi','cdd','cdd','interim'];
        $nameIdx = 0;
        $empNumber = Employee::max('id') ? Employee::max('id') + 1 : 1;

        foreach ($deptEmployeeDist as $deptCode => $config) {
            $dept = $depts[$deptCode];
            $posPool = array_filter($config['positions'], fn($p) => isset($positions[$p]));
            $posPool = array_values($posPool);

            for ($i = 0; $i < $config['count']; $i++) {
                $fn = $firstNames[$nameIdx % count($firstNames)];
                $ln = $lastNames[($nameIdx * 7) % count($lastNames)];
                $nameIdx++;

                $email = strtolower(
                    preg_replace('/[^a-z0-9]/', '', iconv('UTF-8','ASCII//TRANSLIT', $fn)) . '.' .
                    preg_replace('/[^a-z0-9]/', '', iconv('UTF-8','ASCII//TRANSLIT', $ln)) .
                    $empNumber . '@microfinance.mf'
                );

                $user = User::firstOrCreate(['email' => $email], [
                    'name'              => $fn . ' ' . $ln,
                    'email'             => $email,
                    'password'          => $password,
                    'email_verified_at' => now(),
                ]);

                $code     = 'MF-' . str_pad($empNumber, 4, '0', STR_PAD_LEFT);
                $posTitle = $posPool[$i % count($posPool)];
                $pos      = $positions[$posTitle];

                $hireDate = Carbon::now()
                    ->subMonths(rand(1, 60))
                    ->subDays(rand(0, 30))
                    ->toDateString();

                $contract = $contractTypes[array_rand($contractTypes)];
                if ($posTitle === 'Stagiaire') $contract = 'interim';

                $weeklyMinutes = match(true) {
                    str_contains($posTitle, 'Directeur') => 2400,
                    str_contains($posTitle, 'Chef')      => 2250,
                    str_contains($posTitle, 'Senior')    => 2400,
                    default                               => 2400,
                };

                $employee = Employee::firstOrCreate(['employee_code' => $code], [
                    'user_id'              => $user->id,
                    'department_id'        => $dept->id,
                    'position_id'          => $pos->id,
                    'employee_code'        => $code,
                    'hire_date'            => $hireDate,
                    'contract_type'        => $contract,
                    'status'               => $i < ($config['count'] - 1) ? 'active' : ($i % 15 === 0 ? 'suspended' : 'active'),
                    'weekly_hours_minutes' => $weeklyMinutes,
                    'photo_url'            => 'https://ui-avatars.com/api/?name=' . urlencode($fn . ' ' . $ln) . '&background=047857&color=fff&size=128',
                ]);

                // Assign 1-3 random skills
                $randomSkills = collect($skills)->random(rand(1, 3));
                $employee->skills()->syncWithoutDetaching($randomSkills->pluck('id')->toArray());

                $empNumber++;
            }
        }

        $totalEmployees = Employee::count();
        $this->command->info('  ✓ ' . $totalEmployees . ' employees created');

        // ── Shifts for last 3 months ─────────────────────────────────────
        $this->command->info('  Generating shifts (last 3 months)...');
        $employees  = Employee::where('status', 'active')->get();
        $startDate  = Carbon::now()->subMonths(3)->startOfWeek();
        $endDate    = Carbon::now()->addWeeks(2);
        $shiftCount = 0;

        foreach ($employees as $emp) {
            $dept = $emp->department;
            if (!$dept) continue;

            // Pick schedule type based on dept
            $schedDays = match($dept->code) {
                'CREDIT', 'COMM', 'EPARGNE' => [1,2,3,4,5,6],
                'RECOUVR'                   => [1,2,3,4,5],
                default                     => [1,2,3,4,5],
            };
            $shiftStart = match($dept->code) {
                'RECOUVR' => '09:00',
                default   => '08:00',
            };
            $shiftEnd = match($dept->code) {
                'RECOUVR'             => '18:00',
                'CREDIT','EPARGNE'    => '17:00',
                default               => '16:30',
            };

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                if (in_array($current->dayOfWeek, $schedDays)) {
                    $sAt = $current->toDateString() . ' ' . $shiftStart;
                    $eAt = $current->toDateString() . ' ' . $shiftEnd;

                    // ~12% chance of being absent (no shift generated)
                    if (rand(1, 100) <= 88) {
                        // ~5% chance overtime (+2h)
                        $actualEnd = rand(1, 100) <= 5
                            ? $current->toDateString() . ' ' . Carbon::parse($eAt)->addHours(2)->format('H:i')
                            : $eAt;

                        $startC  = Carbon::parse($sAt);
                        $endC    = Carbon::parse($actualEnd);
                        $worked  = max(0, $endC->diffInMinutes($startC) - 60);

                        $status = $current->gt(now()) ? 'planned' : (rand(1,100) <= 95 ? 'completed' : 'cancelled');

                        Shift::firstOrCreate(
                            ['employee_id' => $emp->id, 'start_at' => $sAt],
                            [
                                'employee_id'    => $emp->id,
                                'start_at'       => $sAt,
                                'end_at'         => $actualEnd,
                                'worked_minutes' => $status === 'completed' ? $worked : 0,
                                'status'         => $status,
                            ]
                        );
                        $shiftCount++;
                    }
                }
                $current->addDay();
            }
        }
        $this->command->info('  ✓ ~' . $shiftCount . ' shifts generated');

        // ── Leave Requests ───────────────────────────────────────────────
        $this->command->info('  Generating leave requests...');
        $sampleEmployees = Employee::inRandomOrder()->limit(40)->get();
        $leaveTypes = ['conge_paye', 'rtt', 'maladie', 'sans_solde', 'autre'];
        $leaveCount = 0;

        foreach ($sampleEmployees as $idx => $emp) {
            $startLeave = Carbon::now()->subDays(rand(5, 90))->startOfDay();
            $duration   = rand(1, 15);
            $endLeave   = $startLeave->copy()->addDays($duration);

            $status = match($idx % 5) {
                0 => 'approved',
                1 => 'rejected',
                2 => 'pending',
                3 => 'approved',
                4 => 'cancelled',
            };

            LeaveRequest::create([
                'employee_id'  => $emp->id,
                'type'         => $leaveTypes[array_rand($leaveTypes)],
                'start_date'   => $startLeave->toDateString(),
                'end_date'     => $endLeave->toDateString(),
                'reason'       => 'Demande de congé #' . ($idx + 1),
                'status'       => $status,
                'validated_by' => in_array($status, ['approved','rejected']) ? User::first()->id : null,
                'validated_at' => in_array($status, ['approved','rejected']) ? now() : null,
            ]);
            $leaveCount++;
        }
        $this->command->info('  ✓ ' . $leaveCount . ' leave requests');

        $this->command->info('✅ Microfinance seeding complete!');
        $this->command->info('   Users: ' . User::count());
        $this->command->info('   Employees: ' . Employee::count());
        $this->command->info('   Departments: ' . Department::count());
        $this->command->info('   Positions: ' . Position::count());
    }
}
