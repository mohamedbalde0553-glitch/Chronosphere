<?php

namespace App\Modules\Calendar\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    use HasFactory;

    protected $table = 'cal_calendars';

    protected $fillable = [
        'user_id', 'name', 'color', 'description', 'is_default', 'is_public', 'timezone',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_public'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(EventShare::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
