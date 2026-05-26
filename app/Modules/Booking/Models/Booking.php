<?php

namespace App\Modules\Booking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_bookings';

    protected $fillable = [
        'resource_id', 'user_id', 'title', 'description',
        'start_at', 'end_at', 'duration_minutes', 'attendees_count',
        'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_at'    => 'datetime',
            'end_at'      => 'datetime',
            'approved_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeInRange($query, string $from, string $to)
    {
        return $query->where('start_at', '<', $to)->where('end_at', '>', $from);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
