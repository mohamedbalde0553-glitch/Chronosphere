<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_resources';

    protected $fillable = [
        'category_id', 'name', 'description', 'capacity', 'location', 'color',
        'is_active', 'equipments', 'requires_approval',
        'advance_booking_days', 'max_booking_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'equipments'        => 'array',
            'is_active'         => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ResourceAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
