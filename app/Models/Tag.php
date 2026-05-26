<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'name', 'color', 'slug'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taggables(string $model): MorphToMany
    {
        return $this->morphedByMany($model, 'taggable');
    }
}
