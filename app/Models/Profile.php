<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'profileable_type', 'profileable_id',
        'bio', 'address', 'city', 'country', 'postal_code',
        'date_of_birth', 'gender', 'website', 'social_links',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'social_links'  => 'array',
        ];
    }

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }
}
