<?php

namespace App\Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'uni_subjects';

    protected $fillable = ['code', 'name', 'description', 'coefficient', 'ects', 'color'];

    protected function casts(): array
    {
        return [
            'coefficient' => 'float',
            'ects'        => 'integer',
        ];
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'uni_subject_teacher')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
