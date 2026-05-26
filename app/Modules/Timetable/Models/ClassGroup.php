<?php

namespace App\Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
    use HasFactory;

    protected $table = 'uni_class_groups';

    protected $fillable = ['level_id', 'academic_year_id', 'parent_id', 'name', 'code', 'capacity'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'parent_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
