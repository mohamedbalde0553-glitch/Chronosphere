<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\Resource as BookingResource;
use App\Modules\Booking\Models\ResourceCategory;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\Task;
use App\Modules\Timetable\Models\AcademicYear;
use App\Modules\Timetable\Models\ClassGroup;
use App\Modules\Timetable\Models\Course;
use App\Modules\Timetable\Models\CourseSession;
use App\Modules\Timetable\Models\Faculty;
use App\Modules\Timetable\Models\Level;
use App\Modules\Timetable\Models\Room;
use App\Modules\Timetable\Models\Semester;
use App\Modules\Timetable\Models\Subject;
use App\Modules\Timetable\Models\Teacher;
use App\Modules\Timetable\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AllModulesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // ════════════════════════════════════════════
        // MODULE TIMETABLE
        // ════════════════════════════════════════════
        $this->command->info('📚 Seeding Timetable...');

        $faculty = Faculty::firstOrCreate(['name' => 'Faculté des Sciences Économiques'], ['name' => 'Faculté des Sciences Économiques', 'code' => 'FSEG']);

        $year = AcademicYear::firstOrCreate(['name' => '2025-2026'], [
            'name'       => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date'   => '2026-07-31',
            'is_active'  => true,
        ]);

        $levels = [];
        foreach ([
            ['name' => 'Licence 1', 'order' => 1, '_key' => 'L1'],
            ['name' => 'Licence 2', 'order' => 2, '_key' => 'L2'],
            ['name' => 'Licence 3', 'order' => 3, '_key' => 'L3'],
            ['name' => 'Master 1',  'order' => 4, '_key' => 'M1'],
            ['name' => 'Master 2',  'order' => 5, '_key' => 'M2'],
        ] as $l) {
            $key = $l['_key'];
            unset($l['_key']);
            $levels[$key] = Level::firstOrCreate(['name' => $l['name'], 'faculty_id' => $faculty->id], $l + ['faculty_id' => $faculty->id]);
        }

        $semesters = [];
        foreach ([
            ['name' => 'Semestre 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-31', '_key' => 1],
            ['name' => 'Semestre 2', 'start_date' => '2026-02-01', 'end_date' => '2026-06-30', '_key' => 2],
        ] as $s) {
            $key = $s['_key'];
            unset($s['_key']);
            $semesters[$key] = Semester::firstOrCreate(
                ['academic_year_id' => $year->id, 'name' => $s['name']],
                $s + ['academic_year_id' => $year->id]
            );
        }

        $rooms = [];
        foreach ([
            ['code' => 'A101', 'name' => 'Amphi A',   'capacity' => 200, 'type' => 'amphitheatre'],
            ['code' => 'A102', 'name' => 'Amphi B',   'capacity' => 150, 'type' => 'amphitheatre'],
            ['code' => 'TD01', 'name' => 'Salle TD1', 'capacity' => 35,  'type' => 'classroom'],
            ['code' => 'TD02', 'name' => 'Salle TD2', 'capacity' => 35,  'type' => 'classroom'],
            ['code' => 'TD03', 'name' => 'Salle TD3', 'capacity' => 35,  'type' => 'classroom'],
            ['code' => 'LAB1', 'name' => 'Labo Info', 'capacity' => 30,  'type' => 'laboratory'],
        ] as $r) {
            $rooms[$r['code']] = Room::firstOrCreate(['code' => $r['code']], $r + ['is_active' => true]);
        }

        $subjects = [];
        foreach ([
            ['name' => 'Mathématiques',        'code' => 'MATH101', 'color' => '#3B82F6'],
            ['name' => 'Économie Générale',     'code' => 'ECO101',  'color' => '#10B981'],
            ['name' => 'Comptabilité',          'code' => 'COMPTA',  'color' => '#F59E0B'],
            ['name' => 'Droit des Affaires',    'code' => 'DROIT',   'color' => '#EF4444'],
            ['name' => 'Statistiques',          'code' => 'STAT',    'color' => '#8B5CF6'],
            ['name' => 'Finance d\'entreprise', 'code' => 'FIN201',  'color' => '#EC4899'],
            ['name' => 'Marketing',             'code' => 'MKT201',  'color' => '#06B6D4'],
            ['name' => 'Informatique de gestion','code'=> 'INFO',    'color' => '#F97316'],
        ] as $s) {
            $subjects[$s['code']] = Subject::firstOrCreate(['code' => $s['code']], $s);
        }

        // Time slots (with day_of_week)
        $slotDefs = [
            ['name' => 'Créneau 1 Matin',      'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'order' => 1],
            ['name' => 'Créneau 2 Matin',      'day_of_week' => 1, 'start_time' => '10:15', 'end_time' => '12:15', 'order' => 2],
            ['name' => 'Créneau 1 AM Lun',     'day_of_week' => 1, 'start_time' => '13:00', 'end_time' => '15:00', 'order' => 3],
            ['name' => 'Créneau 2 AM Lun',     'day_of_week' => 1, 'start_time' => '15:15', 'end_time' => '17:15', 'order' => 4],
            ['name' => 'Mar Créneau 1',        'day_of_week' => 2, 'start_time' => '08:00', 'end_time' => '10:00', 'order' => 5],
            ['name' => 'Mar Créneau 2',        'day_of_week' => 2, 'start_time' => '10:15', 'end_time' => '12:15', 'order' => 6],
            ['name' => 'Mar Créneau 3',        'day_of_week' => 2, 'start_time' => '13:00', 'end_time' => '15:00', 'order' => 7],
            ['name' => 'Mer Créneau 1',        'day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '10:00', 'order' => 8],
            ['name' => 'Mer Créneau 2',        'day_of_week' => 3, 'start_time' => '10:15', 'end_time' => '12:15', 'order' => 9],
            ['name' => 'Jeu Créneau 1',        'day_of_week' => 4, 'start_time' => '08:00', 'end_time' => '10:00', 'order' => 10],
            ['name' => 'Jeu Créneau 2',        'day_of_week' => 4, 'start_time' => '10:15', 'end_time' => '12:15', 'order' => 11],
            ['name' => 'Ven Créneau 1',        'day_of_week' => 5, 'start_time' => '08:00', 'end_time' => '10:00', 'order' => 12],
        ];
        $slots = [];
        foreach ($slotDefs as $sl) {
            $slots[] = TimeSlot::firstOrCreate(['name' => $sl['name']], $sl);
        }

        // Teachers (use existing users or create)
        $teacherUsers = [];
        foreach (['Dr. Mamadou Fall', 'Pr. Aissatou Diallo', 'Dr. Cheikh Sow', 'Mme. Fatou Mbaye', 'M. Ibrahima Ba'] as $idx => $name) {
            $email = 'prof' . $idx . '@universite.sn';
            $u = User::firstOrCreate(['email' => $email], ['name' => $name, 'email' => $email, 'password' => bcrypt('password'), 'email_verified_at' => now()]);
            $t = Teacher::firstOrCreate(['user_id' => $u->id], ['user_id' => $u->id, 'speciality' => 'Sciences Économiques', 'contract_type' => 'permanent', 'is_active' => true]);
            $teacherUsers[] = $t;
        }

        // Groups
        $groups = [];
        foreach ([
            ['name' => 'L1-INFO-A', 'code' => 'L1A', 'capacity' => 35, 'level_id' => $levels['L1']->id, 'academic_year_id' => $year->id],
            ['name' => 'L1-INFO-B', 'code' => 'L1B', 'capacity' => 35, 'level_id' => $levels['L1']->id, 'academic_year_id' => $year->id],
            ['name' => 'L2-ECO-A',  'code' => 'L2A', 'capacity' => 30, 'level_id' => $levels['L2']->id, 'academic_year_id' => $year->id],
            ['name' => 'L3-FIN-A',  'code' => 'L3A', 'capacity' => 28, 'level_id' => $levels['L3']->id, 'academic_year_id' => $year->id],
            ['name' => 'M1-GES-A',  'code' => 'M1A', 'capacity' => 20, 'level_id' => $levels['M1']->id, 'academic_year_id' => $year->id],
        ] as $g) {
            $groups[$g['code']] = ClassGroup::firstOrCreate(['code' => $g['code']], $g);
        }

        // Courses + Sessions for current semester
        $courseConfigs = [
            ['subject' => 'MATH101', 'group' => 'L1A', 'teacher' => 0, 'slot_idx' => 0, 'room' => 'TD01'],
            ['subject' => 'ECO101',  'group' => 'L1A', 'teacher' => 1, 'slot_idx' => 1, 'room' => 'A101'],
            ['subject' => 'COMPTA',  'group' => 'L2A', 'teacher' => 2, 'slot_idx' => 4, 'room' => 'TD02'],
            ['subject' => 'DROIT',   'group' => 'L2A', 'teacher' => 3, 'slot_idx' => 5, 'room' => 'A102'],
            ['subject' => 'STAT',    'group' => 'L1B', 'teacher' => 0, 'slot_idx' => 7, 'room' => 'TD03'],
            ['subject' => 'FIN201',  'group' => 'L3A', 'teacher' => 4, 'slot_idx' => 9, 'room' => 'A101'],
            ['subject' => 'MKT201',  'group' => 'M1A', 'teacher' => 1, 'slot_idx' => 2, 'room' => 'TD01'],
            ['subject' => 'INFO',    'group' => 'L1B', 'teacher' => 2, 'slot_idx' => 10, 'room' => 'LAB1'],
        ];

        $sem = $semesters[2];
        $sessionCount = 0;
        foreach ($courseConfigs as $cfg) {
            $course = Course::firstOrCreate(
                ['subject_id' => $subjects[$cfg['subject']]->id, 'class_group_id' => $groups[$cfg['group']]->id, 'semester_id' => $sem->id],
                [
                    'subject_id'            => $subjects[$cfg['subject']]->id,
                    'teacher_id'            => $teacherUsers[$cfg['teacher']]->id,
                    'class_group_id'        => $groups[$cfg['group']]->id,
                    'semester_id'           => $sem->id,
                    'weekly_volume_minutes' => 120,
                ]
            );

            // Generate ~8 sessions across the semester
            $slot = $slots[$cfg['slot_idx']];
            $roomObj = $rooms[$cfg['room']];
            $current = Carbon::parse($sem->start_date);
            $end     = Carbon::parse($sem->end_date);
            $sessCreated = 0;

            while ($current->lte($end) && $sessCreated < 16) {
                if ($current->dayOfWeek === ($slot->day_of_week ?? 1)) {
                    $startAt = $current->toDateString() . ' ' . $slot->start_time;
                    $endAt   = $current->toDateString() . ' ' . $slot->end_time;
                    CourseSession::firstOrCreate(
                        ['course_id' => $course->id, 'start_at' => $startAt],
                        ['course_id' => $course->id, 'room_id' => $roomObj->id, 'start_at' => $startAt, 'end_at' => $endAt, 'status' => $current->isPast() ? 'completed' : 'scheduled', 'created_by' => $admin->id]
                    );
                    $sessCreated++;
                    $sessionCount++;
                    $current->addWeeks(1);
                } else {
                    $current->addDay();
                }
            }
        }
        $this->command->info('  ✓ Timetable: ' . count($rooms) . ' rooms, ' . count($subjects) . ' subjects, ' . count($groups) . ' groups, ' . $sessionCount . ' sessions');

        // ════════════════════════════════════════════
        // MODULE BOOKING
        // ════════════════════════════════════════════
        $this->command->info('📅 Seeding Booking...');

        $bookingCategories = [];
        foreach ([
            ['name' => 'Salle de Réunion', 'color' => '#3B82F6', 'description' => 'Salles pour réunions internes'],
            ['name' => 'Véhicule',         'color' => '#10B981', 'description' => 'Véhicules de service'],
            ['name' => 'Matériel IT',      'color' => '#8B5CF6', 'description' => 'Vidéoprojecteurs, laptops'],
            ['name' => 'Salle de Formation','color'=> '#F59E0B', 'description' => 'Salles équipées pour formations'],
        ] as $cat) {
            $bookingCategories[$cat['name']] = ResourceCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $resources = [];
        foreach ([
            ['name' => 'Salle Conseil',     'category' => 'Salle de Réunion', 'capacity' => 20, 'description' => 'Grande salle de conseil, projecteur inclus'],
            ['name' => 'Salle Opérations',  'category' => 'Salle de Réunion', 'capacity' => 10, 'description' => 'Salle de réunion opérationnelle'],
            ['name' => 'Salle Crédit',      'category' => 'Salle de Réunion', 'capacity' => 8,  'description' => 'Salle dédiée aux comités de crédit'],
            ['name' => 'Toyota Hilux #1',   'category' => 'Véhicule',         'capacity' => 5,  'description' => 'Véhicule terrain direction'],
            ['name' => 'Toyota Hilux #2',   'category' => 'Véhicule',         'capacity' => 5,  'description' => 'Véhicule terrain recouvrement'],
            ['name' => 'Vidéoprojecteur HD','category' => 'Matériel IT',      'capacity' => 1,  'description' => 'Sony VPL-EX435'],
            ['name' => 'Salle Formation A', 'category' => 'Salle de Formation','capacity'=> 30, 'description' => 'Salle de formation principale'],
        ] as $r) {
            $resources[] = BookingResource::firstOrCreate(['name' => $r['name']], [
                'name'        => $r['name'],
                'category_id' => $bookingCategories[$r['category']]->id,
                'capacity'    => $r['capacity'],
                'description' => $r['description'],
                'is_active'   => true,
            ]);
        }

        // Create some bookings
        $bookingStatuses = ['approved','pending','approved','rejected','approved'];
        foreach ($resources as $ridx => $res) {
            for ($b = 0; $b < 4; $b++) {
                $bookDate = Carbon::now()->addDays(rand(-10, 20))->setHour(rand(8, 16))->setMinute(0)->setSecond(0);
                Booking::create([
                    'resource_id'      => $res->id,
                    'user_id'          => $admin->id,
                    'title'            => 'Réservation ' . $res->name . ' #' . ($ridx * 4 + $b + 1),
                    'description'      => 'Réservation test',
                    'start_at'         => $bookDate->toDateTimeString(),
                    'end_at'           => $bookDate->copy()->addHours(2)->toDateTimeString(),
                    'duration_minutes' => 120,
                    'status'           => $bookingStatuses[($ridx + $b) % count($bookingStatuses)],
                    'attendees_count'  => rand(2, 15),
                ]);
            }
        }
        $this->command->info('  ✓ Booking: ' . count($resources) . ' resources, ' . (count($resources) * 4) . ' reservations');

        // ════════════════════════════════════════════
        // MODULE PROJECT
        // ════════════════════════════════════════════
        $this->command->info('📋 Seeding Projects...');

        $projectsData = [
            ['name' => 'Digitalisation Crédit', 'description' => 'Migration du processus de crédit vers le Core Banking System CBS', 'color' => '#3B82F6', 'status' => 'active'],
            ['name' => 'Formation Agents 2026',  'description' => 'Programme de formation annuel pour tous les agents de crédit', 'color' => '#10B981', 'status' => 'active'],
            ['name' => 'Refonte Site Web',       'description' => 'Nouveau site institutionnel + portail client', 'color' => '#8B5CF6', 'status' => 'planning'],
            ['name' => 'Audit Conformité AML',   'description' => 'Audit interne conformité anti-blanchiment', 'color' => '#EF4444', 'status' => 'active'],
        ];

        $tasksByProject = [
            'Digitalisation Crédit' => [
                ['title' => 'Analyse des besoins fonctionnels', 'status' => 'done',        'priority' => 'high'],
                ['title' => 'Paramétrage module crédit CBS',    'status' => 'in_progress',  'priority' => 'urgent'],
                ['title' => 'Migration données historiques',    'status' => 'todo',         'priority' => 'high'],
                ['title' => 'Tests UAT avec équipes crédit',   'status' => 'todo',         'priority' => 'medium'],
                ['title' => 'Formation utilisateurs CBS',       'status' => 'todo',         'priority' => 'medium'],
                ['title' => 'Go-live et support post-démarrage','status'=> 'todo',         'priority' => 'high'],
            ],
            'Formation Agents 2026' => [
                ['title' => 'Conception du programme',          'status' => 'done',        'priority' => 'medium'],
                ['title' => 'Module crédit microentreprise',    'status' => 'in_progress', 'priority' => 'high'],
                ['title' => 'Module conformité KYC/AML',        'status' => 'todo',        'priority' => 'high'],
                ['title' => 'Module recouvrement amiable',      'status' => 'todo',        'priority' => 'medium'],
                ['title' => 'Évaluation finale agents',         'status' => 'todo',        'priority' => 'low'],
            ],
            'Refonte Site Web' => [
                ['title' => 'Cahier des charges UX',            'status' => 'in_progress', 'priority' => 'medium'],
                ['title' => 'Maquettes design',                 'status' => 'todo',        'priority' => 'medium'],
                ['title' => 'Développement frontend',           'status' => 'todo',        'priority' => 'high'],
                ['title' => 'Portail client (espace en ligne)', 'status' => 'todo',        'priority' => 'high'],
            ],
            'Audit Conformité AML' => [
                ['title' => 'Revue politique AML existante',    'status' => 'done',        'priority' => 'urgent'],
                ['title' => 'Tests transactions suspectes',     'status' => 'in_progress', 'priority' => 'urgent'],
                ['title' => 'Rapport d\'audit préliminaire',    'status' => 'review',      'priority' => 'high'],
                ['title' => 'Plan de remédiation',              'status' => 'todo',        'priority' => 'high'],
                ['title' => 'Validation par la direction',      'status' => 'todo',        'priority' => 'medium'],
            ],
        ];

        foreach ($projectsData as $pidx => $pd) {
            $project = Project::firstOrCreate(['name' => $pd['name']], [
                'name'        => $pd['name'],
                'description' => $pd['description'],
                'color'       => $pd['color'],
                'status'      => $pd['status'],
                'owner_id'    => $admin->id,
                'start_date'  => Carbon::now()->subDays(rand(10, 60))->toDateString(),
                'due_date'    => Carbon::now()->addDays(rand(30, 120))->toDateString(),
            ]);

            $taskOrder = 1;
            foreach ($tasksByProject[$pd['name']] ?? [] as $td) {
                Task::firstOrCreate(['project_id' => $project->id, 'name' => $td['title']], [
                    'project_id'  => $project->id,
                    'name'        => $td['title'],
                    'status'      => $td['status'],
                    'priority'    => $td['priority'],
                    'sort_order'  => $taskOrder++,
                    'progress'    => $td['status'] === 'done' ? 100 : ($td['status'] === 'in_progress' ? rand(20, 80) : 0),
                    'assigned_to' => $admin->id,
                    'created_by'  => $admin->id,
                    'due_date'    => Carbon::now()->addDays(rand(5, 60))->toDateString(),
                ]);
            }
        }
        $this->command->info('  ✓ Projects: ' . Project::count() . ' projects, ' . Task::count() . ' tasks');

        $this->command->info('✅ All modules seeded!');
    }
}
