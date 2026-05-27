<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkScheduleDay extends Model
{
    protected $table = 'hr_work_schedule_days';

    protected $fillable = [
        'work_schedule_id', 'day_of_week', 'start_time', 'end_time',
        'break_minutes', 'is_overtime_eligible', 'multiplier',
    ];

    protected function casts(): array
    {
        return [
            'is_overtime_eligible' => 'boolean',
            'multiplier'           => 'float',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    public function workedMinutes(): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);

        $start = (int)$sh * 60 + (int)$sm;
        $end   = (int)$eh * 60 + (int)$em;

        if ($end <= $start) {
            $end += 24 * 60; // passage minuit
        }

        return max(0, $end - $start - $this->break_minutes);
    }

    public static function dayLabel(int $day): string
    {
        return ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'][$day] ?? '?';
    }
}
