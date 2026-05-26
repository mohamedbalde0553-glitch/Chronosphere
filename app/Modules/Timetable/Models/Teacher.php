<?php

namespace App\Modules\Timetable\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'uni_teachers';

    protected $fillable = [
        'user_id', 'employee_code', 'title', 'speciality',
        'contract_type', 'weekly_hours_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'uni_subject_teacher')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function courseSessions(): HasMany
    {
        return $this->hasMany(CourseSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
