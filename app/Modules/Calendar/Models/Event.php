<?php

namespace App\Modules\Calendar\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cal_events';

    protected $fillable = [
        'calendar_id', 'category_id', 'user_id', 'parent_event_id',
        'title', 'description', 'start_at', 'end_at', 'is_all_day',
        'location', 'url', 'color', 'status', 'visibility',
        'recurrence_rule', 'recurrence_end_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at'        => 'datetime',
            'end_at'          => 'datetime',
            'recurrence_end_at' => 'datetime',
            'is_all_day'      => 'boolean',
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(EventReminder::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->where('start_at', '<', $to)->where('end_at', '>', $from);
    }

    public function scopeVisible($query)
    {
        return $query->where('visibility', 'public');
    }
}
