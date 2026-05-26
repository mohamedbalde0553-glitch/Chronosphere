<?php

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $table = 'hr_skills';

    public $timestamps = false;

    protected $fillable = ['name', 'category'];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'hr_employee_skill')
            ->withPivot('level');
    }
}
