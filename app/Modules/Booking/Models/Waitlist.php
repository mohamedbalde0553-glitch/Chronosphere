<?php

namespace App\Modules\Booking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $table = 'booking_waitlist';

    protected $fillable = [
        'resource_id', 'user_id',
        'requested_start_at', 'requested_end_at', 'duration_minutes',
        'status', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_start_at' => 'datetime',
            'requested_end_at'   => 'datetime',
            'notified_at'        => 'datetime',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
