<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceCategory extends Model
{
    use HasFactory;

    protected $table = 'booking_resource_categories';

    protected $fillable = ['name', 'icon', 'color', 'description'];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'category_id');
    }
}
