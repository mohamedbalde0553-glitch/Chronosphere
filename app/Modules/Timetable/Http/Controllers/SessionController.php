<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\CourseSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id'        => 'required|exists:uni_courses,id',
            'room_id'          => 'nullable|exists:uni_rooms,id',
            'start_at'         => 'required|date',
            'end_at'           => 'required|date|after:start_at',
            'notes'            => 'nullable|string',
        ]);

        $conflicts = $this->detectConflicts(
            null, $data['course_id'], $data['room_id'] ?? null, $data['start_at'], $data['end_at']
        );

        if ($conflicts && !$request->boolean('force')) {
            return response()->json(['conflicts' => $conflicts], 409);
        }

        $session = CourseSession::create($data + ['status' => 'scheduled']);

        $session->load(['course.subject', 'room']);

        return response()->json([
            'id'              => $session->id,
            'title'           => $session->course->subject->name,
            'start'           => $session->start_at->toIso8601String(),
            'end'             => $session->end_at->toIso8601String(),
            'backgroundColor' => $session->course->subject->color ?? '#1E40AF',
            'borderColor'     => $session->course->subject->color ?? '#1E40AF',
        ], 201);
    }

    public function update(Request $request, CourseSession $session): JsonResponse
    {
        $data = $request->validate([
            'start_at' => 'sometimes|required|date',
            'end_at'   => 'sometimes|required|date|after:start_at',
            'room_id'  => 'nullable|exists:uni_rooms,id',
            'status'   => 'nullable|string|in:scheduled,completed,cancelled',
            'notes'    => 'nullable|string',
        ]);

        if (isset($data['start_at'], $data['end_at'])) {
            $roomId    = $data['room_id'] ?? $session->room_id;
            $conflicts = $this->detectConflicts(
                $session->id, $session->course_id, $roomId, $data['start_at'], $data['end_at']
            );
            if ($conflicts && !$request->boolean('force')) {
                return response()->json(['conflicts' => $conflicts], 409);
            }
        }

        $session->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroy(CourseSession $session): JsonResponse
    {
        $session->delete();
        return response()->json(['ok' => true]);
    }

    public function conflicts(Request $request, CourseSession $session): JsonResponse
    {
        $start = $request->query('start', $session->start_at);
        $end   = $request->query('end', $session->end_at);
        $roomId = $request->query('room_id', $session->room_id);

        return response()->json([
            'conflicts' => $this->detectConflicts($session->id, $session->course_id, $roomId, $start, $end),
        ]);
    }

    private function detectConflicts(?int $excludeId, int $courseId, ?int $roomId, string $start, string $end): array
    {
        $conflicts = [];
        $base = CourseSession::where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->where('status', '!=', 'cancelled');

        if ($excludeId) $base->where('id', '!=', $excludeId);

        // Salle déjà occupée
        if ($roomId && (clone $base)->where('room_id', $roomId)->exists()) {
            $conflicts[] = ['type' => 'room', 'message' => 'La salle est déjà réservée sur ce créneau.'];
        }

        // Enseignant déjà occupé
        $course = \App\Modules\Timetable\Models\Course::find($courseId);
        if ($course) {
            $teacherSessionExists = (clone $base)
                ->whereHas('course', fn ($q) => $q->where('teacher_id', $course->teacher_id))
                ->exists();
            if ($teacherSessionExists) {
                $conflicts[] = ['type' => 'teacher', 'message' => "L'enseignant a déjà un cours sur ce créneau."];
            }

            // Groupe déjà occupé
            $groupSessionExists = (clone $base)
                ->whereHas('course', fn ($q) => $q->where('class_group_id', $course->class_group_id))
                ->exists();
            if ($groupSessionExists) {
                $conflicts[] = ['type' => 'group', 'message' => 'Le groupe a déjà un cours sur ce créneau.'];
            }
        }

        return $conflicts;
    }
}
