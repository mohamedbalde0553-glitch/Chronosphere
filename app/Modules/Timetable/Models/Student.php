<?php

namespace App\Modules\Timetable\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $table = 'uni_students';

    protected $fillable = [
        'user_id', 'class_group_id', 'student_code', 'enrollment_date',
    ];

    protected function casts(): array
    {
        return ['enrollment_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
