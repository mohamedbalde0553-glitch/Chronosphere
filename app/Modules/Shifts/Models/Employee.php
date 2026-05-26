<?php

namespace App\Modules\Shifts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_employees';

    protected $fillable = [
        'user_id', 'department_id', 'position_id', 'employee_code',
        'hire_date', 'contract_type', 'status', 'photo_url',
        'weekly_hours_minutes', 'max_daily_minutes', 'min_rest_minutes',
    ];

    protected function casts(): array
    {
        return ['hire_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'hr_employee_skill')
            ->withPivot('level');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
