<?php

namespace App\Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $table = 'uni_faculties';

    protected $fillable = ['name', 'code', 'color', 'description'];

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }
}
