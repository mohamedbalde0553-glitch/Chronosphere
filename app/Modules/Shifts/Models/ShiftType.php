<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftType extends Model
{
    use HasFactory;

    protected $table = 'hr_shift_types';

    protected $fillable = [
        'name', 'start_time', 'end_time', 'color', 'is_night', 'overtime_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'is_night'             => 'boolean',
            'overtime_multiplier'  => 'float',
        ];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
