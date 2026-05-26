<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $table = 'hr_positions';

    protected $fillable = ['name', 'description', 'level'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
