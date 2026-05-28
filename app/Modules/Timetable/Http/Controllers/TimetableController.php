<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\AcademicYear;
use App\Modules\Timetable\Models\ClassGroup;
use App\Modules\Timetable\Models\Course;
use App\Modules\Timetable\Models\CourseSession;
use App\Modules\Timetable\Models\Room;
use App\Modules\Timetable\Models\Student;
use App\Modules\Timetable\Models\Subject;
use App\Modules\Timetable\Models\Teacher;
use App\Modules\Timetable\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        // Étudiant : redirige directement sur son emploi du temps
        if ($user->hasRole('uni_student')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                return redirect()->route('timetable.schedule', [
                    'by' => 'group',
                    'id' => $student->class_group_id,
                ]);
            }
        }

        // Enseignant : redirige sur son planning
        if ($user->hasRole('uni_teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                return redirect()->route('timetable.schedule', [
                    'by' => 'teacher',
                    'id' => $teacher->id,
                ]);
            }
        }

        $stats = [
            'rooms'    => Room::count(),
            'subjects' => Subject::count(),
            'groups'   => ClassGroup::count(),
            'teachers' => Teacher::count(),
            'sessions' => CourseSession::whereDate('start_at', '>=', now()->startOfWeek())->count(),
        ];

        $currentYear = AcademicYear::where('is_active', true)->first();
        $upcomingSessions = CourseSession::with(['course.subject', 'course.teacher.user', 'room'])
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addDays(7))
            ->where('status', 'scheduled')
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        return view('modules.timetable.index', compact('stats', 'currentYear', 'upcomingSessions'));
    }

    public function schedule(Request $request): View
    {
        $user      = auth()->user();
        $isStudent = $user->hasRole('uni_student');
        $isTeacher = $user->hasRole('uni_teacher');

        $groups    = ClassGroup::with('level')->orderBy('name')->get();
        $teachers  = Teacher::with('user')->orderBy('id')->get();
        $rooms     = Room::where('is_active', true)->orderBy('code')->get();
        $subjects  = Subject::orderBy('name')->get();
        $courses   = Course::with(['subject', 'teacher.user', 'classGroup', 'semester'])->get();
        $timeSlots = TimeSlot::orderBy('order')->orderBy('start_time')->get();

        $filterType = $request->get('by', 'group');
        $filterId   = $request->get('id', $groups->first()?->id);

        // Étudiant : forcer sur son groupe, pas de contrôle admin
        if ($isStudent) {
            $student    = Student::where('user_id', $user->id)->first();
            $filterType = 'group';
            $filterId   = $student?->class_group_id ?? $groups->first()?->id;
        }

        // Enseignant : forcer sur son propre planning
        if ($isTeacher) {
            $teacher    = Teacher::where('user_id', $user->id)->first();
            $filterType = 'teacher';
            $filterId   = $teacher?->id ?? $teachers->first()?->id;
        }

        return view('modules.timetable.schedule', compact(
            'groups', 'teachers', 'rooms', 'subjects', 'courses', 'timeSlots',
            'filterType', 'filterId', 'isStudent', 'isTeacher'
        ));
    }

    public function feed(Request $request): JsonResponse
    {
        $user      = auth()->user();
        $start     = $request->query('start', now()->startOfWeek()->toDateTimeString());
        $end       = $request->query('end', now()->endOfWeek()->toDateTimeString());

        $filterType = $request->query('by', 'group');
        $filterId   = $request->query('id');

        // Sécurité : étudiant ne peut voir QUE son groupe (ignore les paramètres URL)
        if ($user->hasRole('uni_student')) {
            $student    = Student::where('user_id', $user->id)->first();
            $filterType = 'group';
            $filterId   = $student?->class_group_id;
        }

        // Sécurité : enseignant ne peut voir QUE son planning
        if ($user->hasRole('uni_teacher')) {
            $teacher    = Teacher::where('user_id', $user->id)->first();
            $filterType = 'teacher';
            $filterId   = $teacher?->id;
        }

        $query = CourseSession::with(['course.subject', 'course.teacher.user', 'course.classGroup', 'room'])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->where('status', '!=', 'cancelled');

        if ($filterId) {
            match ($filterType) {
                'group'   => $query->whereHas('course', fn ($q) => $q->where('class_group_id', $filterId)),
                'teacher' => $query->whereHas('course', fn ($q) => $q->where('teacher_id', $filterId)),
                'room'    => $query->where('room_id', $filterId),
                default   => null,
            };
        }

        return response()->json($query->get()->map(fn (CourseSession $s) => [
            'id'              => $s->id,
            'title'           => $s->course->subject->name
                                 . ($s->room ? ' — ' . $s->room->code : ''),
            'start'           => $s->start_at->toIso8601String(),
            'end'             => $s->end_at->toIso8601String(),
            'backgroundColor' => $s->course->subject->color ?? '#1E40AF',
            'borderColor'     => $s->course->subject->color ?? '#1E40AF',
            'extendedProps'   => [
                'subject'    => $s->course->subject->name,
                'teacher'    => $s->course->teacher?->user?->name,
                'group'      => $s->course->classGroup?->name,
                'room'       => $s->room?->code,
                'room_name'  => $s->room?->name,
                'course_id'  => $s->course_id,
                'room_id'    => $s->room_id,
                'status'     => $s->status,
                'notes'      => $s->notes,
            ],
        ]));
    }
}
