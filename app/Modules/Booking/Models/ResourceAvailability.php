<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAvailability extends Model
{
    protected $table = 'booking_resource_availabilities';

    protected $fillable = [
        'resource_id', 'day_of_week', 'specific_date', 'start_time', 'end_time', 'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'is_closed'     => 'boolean',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
