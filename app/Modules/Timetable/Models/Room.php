<?php

namespace App\Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $table = 'uni_rooms';

    protected $fillable = ['code', 'name', 'capacity', 'type', 'building', 'floor', 'equipments', 'is_active'];

    protected function casts(): array
    {
        return [
            'equipments' => 'array',
            'is_active'  => 'boolean',
        ];
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
