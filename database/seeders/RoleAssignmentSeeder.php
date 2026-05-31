<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Timetable\Models\ClassGroup;
use App\Modules\Timetable\Models\Student;
use App\Modules\Timetable\Models\Teacher;
use App\Modules\Shifts\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ── 1. Étudiants par groupe ───────────────────────────────────────
        $this->command->info('Creating students...');

        $studentData = [
            ['group_code' => 'L1A', 'students' => [
                ['code' => '2025-L1A-001', 'name' => 'Fatou Diallo'],
                ['code' => '2025-L1A-002', 'name' => 'Ibrahim Sow'],
                ['code' => '2025-L1A-003', 'name' => 'Mariama Bah'],
            ]],
            ['group_code' => 'L1B', 'students' => [
                ['code' => '2025-L1B-001', 'name' => 'Ousmane Camara'],
                ['code' => '2025-L1B-002', 'name' => 'Aissatou Barry'],
            ]],
            ['group_code' => 'L2A', 'students' => [
                ['code' => '2024-L2A-001', 'name' => 'Mamadou Baldé'],
                ['code' => '2024-L2A-002', 'name' => 'Kadiatou Kouyaté'],
            ]],
            ['group_code' => 'L3A', 'students' => [
                ['code' => '2023-L3A-001', 'name' => 'Thierno Diallo'],
                ['code' => '2023-L3A-002', 'name' => 'Mariam Keita'],
            ]],
            ['group_code' => 'M1A', 'students' => [
                ['code' => '2022-M1A-001', 'name' => 'Alpha Condé'],
            ]],
        ];

        foreach ($studentData as $gData) {
            $group = ClassGroup::where('code', $gData['group_code'])->first();
            if (!$group) continue;

            foreach ($gData['students'] as $s) {
                // Email dérivé du matricule (sans tirets et majuscules)
                $email = strtolower(str_replace(['-', ' '], ['', '.'], $s['code'])) . '@univ.local';

                $user = User::firstOrCreate(['email' => $email], [
                    'name'               => $s['name'],
                    'email'              => $email,
                    'password'           => $password,
                    'email_verified_at'  => now(),
                ]);
                $user->syncRoles(['uni_student']);

                Student::firstOrCreate(['student_code' => $s['code']], [
                    'user_id'         => $user->id,
                    'class_group_id'  => $group->id,
                    'student_code'    => $s['code'],
                    'enrollment_date' => now()->startOfYear(),
                ]);

                $this->command->line("  ✓ étudiant {$s['code']} ({$gData['group_code']}) → {$email}");
            }
        }

        // ── 2. Rôle uni_teacher sur les professeurs existants ─────────────
        $this->command->info('Assigning uni_teacher role...');
        $teachers = Teacher::with('user')->get();
        foreach ($teachers as $t) {
            if ($t->user) {
                $t->user->syncRoles(['uni_teacher']);
                $this->command->line("  ✓ enseignant {$t->user->email}");
            }
        }

        // ── 3. Rôle hr_employee sur les employés microfinance ─────────────
        $this->command->info('Assigning hr_employee role to microfinance employees...');
        $employees = Employee::with('user')->get();
        $count = 0;
        foreach ($employees as $emp) {
            if ($emp->user) {
                // Garder super_admin intact
                if ($emp->user->hasRole('super_admin')) continue;
                $emp->user->syncRoles(['hr_employee']);
                $count++;
            }
        }
        $this->command->info("  ✓ {$count} employés → hr_employee");

        // ── 4. Rôle hr_manager sur les responsables RH ───────────────────
        $this->command->info('Assigning hr_manager to RH department heads...');
        $hrDept = \App\Modules\Shifts\Models\Department::where('code', 'RH')->first();
        if ($hrDept) {
            $hrManagers = Employee::where('department_id', $hrDept->id)
                ->whereHas('position', fn($q) => $q->where('title', 'like', 'Responsable%'))
                ->with('user')
                ->get();
            foreach ($hrManagers as $m) {
                if ($m->user && !$m->user->hasRole('super_admin')) {
                    $m->user->syncRoles(['hr_manager']);
                    $this->command->line("  ✓ hr_manager: {$m->user->email}");
                }
            }
        }

        $this->command->info('✅ Role assignment complete.');
        $this->command->info('');
        $this->command->info('Comptes étudiants (login / mdp: password):');
        $this->command->info('  2025l1a001@univ.local — Fatou Diallo (L1-INFO-A)');
        $this->command->info('  2025l1b001@univ.local — Ousmane Camara (L1-INFO-B)');
        $this->command->info('  2024l2a001@univ.local — Mamadou Baldé (L2-ECO-A)');
        $this->command->info('  2023l3a001@univ.local — Thierno Diallo (L3-FIN-A)');
        $this->command->info('  2022m1a001@univ.local — Alpha Condé (M1-GES-A)');
        $this->command->info('');
        $this->command->info('Comptes employés (exemple):');
        $this->command->info('  prenom.nom1@microfinance.mf / password → planning personnel uniquement');
    }
}
