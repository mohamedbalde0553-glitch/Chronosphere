<?php

namespace App\Modules\Calendar\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminder extends Model
{
    protected $table = 'cal_event_reminders';

    protected $fillable = ['event_id', 'user_id', 'remind_at', 'method', 'sent_at'];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'sent_at'   => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->whereNull('sent_at')->where('remind_at', '<=', now());
    }
}
