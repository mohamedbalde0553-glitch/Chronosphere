<?php

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Models\Course;
use App\Modules\Timetable\Models\CourseSession;
use App\Modules\Timetable\Models\TimeSlot;
use Carbon\Carbon;

class TimetableGeneratorService
{
    /**
     * Génère toutes les CourseSession d'un cours sur toute la durée de son semestre
     * en se basant sur le TimeSlot (jour + heures).
     *
     * @param  int       $courseId
     * @param  int       $timeSlotId
     * @param  int|null  $roomId
     * @param  string[]  $excludedDates  dates ISO à sauter (ex: jours fériés)
     * @return array{created: int, skipped_conflict: int, skipped_duplicate: int}
     */
    public function generate(int $courseId, int $timeSlotId, ?int $roomId, array $excludedDates = []): array
    {
        $course   = Course::with('semester')->findOrFail($courseId);
        $slot     = TimeSlot::findOrFail($timeSlotId);
        $semester = $course->semester;

        $created           = 0;
        $skippedDuplicate  = 0;
        $skippedConflict   = 0;

        $current = Carbon::parse($semester->start_date);
        $end     = Carbon::parse($semester->end_date);

        while ($current->lte($end)) {
            // Vérifier si le jour de la semaine correspond
            if ($current->dayOfWeek == $slot->day_of_week && !in_array($current->toDateString(), $excludedDates)) {
                $startAt = $current->toDateString() . ' ' . $slot->start_time;
                $endAt   = $current->toDateString() . ' ' . $slot->end_time;

                // Passage minuit
                if ($slot->end_time < $slot->start_time) {
                    $endAt = $current->copy()->addDay()->toDateString() . ' ' . $slot->end_time;
                }

                // Doublon : même cours, même heure
                $duplicate = CourseSession::where('course_id', $courseId)
                    ->where('start_at', $startAt)
                    ->exists();

                if ($duplicate) {
                    $skippedDuplicate++;
                    $current->addDay();
                    continue;
                }

                // Conflit salle
                if ($roomId) {
                    $roomConflict = CourseSession::where('room_id', $roomId)
                        ->where('start_at', '<', $endAt)
                        ->where('end_at', '>', $startAt)
                        ->where('status', '!=', 'cancelled')
                        ->exists();

                    if ($roomConflict) {
                        $skippedConflict++;
                        $current->addDay();
                        continue;
                    }
                }

                CourseSession::create([
                    'course_id'  => $courseId,
                    'room_id'    => $roomId,
                    'start_at'   => $startAt,
                    'end_at'     => $endAt,
                    'status'     => 'scheduled',
                    'notes'      => 'Généré automatiquement — ' . $slot->name,
                    'created_by' => auth()->id() ?? 1,
                ]);

                $created++;
            }

            $current->addDay();
        }

        return [
            'created'           => $created,
            'skipped_duplicate' => $skippedDuplicate,
            'skipped_conflict'  => $skippedConflict,
        ];
    }
}
