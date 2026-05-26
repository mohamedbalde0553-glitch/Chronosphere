<?php

namespace App\Modules\Project\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_projects';

    protected $fillable = [
        'owner_id', 'name', 'description', 'color', 'status',
        'start_date', 'due_date', 'completed_at', 'budget',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'due_date'     => 'date',
            'completed_at' => 'datetime',
            'budget'       => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_teams')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function rootTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('parent_task_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
