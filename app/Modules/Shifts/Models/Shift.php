<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_shifts';

    protected $fillable = [
        'employee_id', 'shift_type_id', 'start_at', 'end_at',
        'actual_start_at', 'actual_end_at',
        'worked_minutes', 'overtime_minutes', 'break_minutes',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_at'        => 'datetime',
            'end_at'          => 'datetime',
            'actual_start_at' => 'datetime',
            'actual_end_at'   => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->where('start_at', '<', $to)->where('end_at', '>', $from);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }
}
