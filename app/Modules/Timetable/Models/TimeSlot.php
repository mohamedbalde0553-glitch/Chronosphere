<?php

namespace App\Modules\Timetable\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'uni_time_slots';

    public $timestamps = false;

    protected $fillable = ['name', 'start_time', 'end_time', 'day_of_week', 'sort_order'];
}
