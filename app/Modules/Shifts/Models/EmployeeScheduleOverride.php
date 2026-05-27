<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeScheduleOverride extends Model
{
    protected $table = 'hr_employee_schedule_overrides';

    protected $fillable = [
        'employee_id', 'work_schedule_id',
        'override_start_date', 'override_end_date', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'override_start_date' => 'date',
            'override_end_date'   => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }
}
