<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\AcademicYear;
use App\Modules\Timetable\Models\ClassGroup;
use App\Modules\Timetable\Models\Course;
use App\Modules\Timetable\Models\CourseSession;
use App\Modules\Timetable\Models\Room;
use App\Modules\Timetable\Models\Subject;
use App\Modules\Timetable\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(): View
    {
        $stats = [
            'rooms'    => Room::count(),
            'subjects' => Subject::count(),
            'groups'   => ClassGroup::count(),
            'teachers' => Teacher::count(),
            'sessions' => CourseSession::whereDate('start_at', '>=', now()->startOfWeek())->count(),
        ];

        $currentYear = AcademicYear::where('is_current', true)->first();
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
        $groups   = ClassGroup::with('level')->orderBy('name')->get();
        $teachers = Teacher::with('user')->orderBy('id')->get();
        $rooms    = Room::where('is_active', true)->orderBy('code')->get();
        $subjects = Subject::orderBy('name')->get();
        $courses  = Course::with(['subject', 'teacher.user', 'classGroup'])->get();

        $filterType = $request->get('by', 'group');
        $filterId   = $request->get('id', $groups->first()?->id);

        return view('modules.timetable.schedule', compact(
            'groups', 'teachers', 'rooms', 'subjects', 'courses', 'filterType', 'filterId'
        ));
    }

    public function feed(Request $request): JsonResponse
    {
        $start      = $request->query('start', now()->startOfWeek()->toDateTimeString());
        $end        = $request->query('end', now()->endOfWeek()->toDateTimeString());
        $filterType = $request->query('by', 'group');
        $filterId   = $request->query('id');

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
