<?php

namespace App\Modules\Shifts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_employees';

    /**
     * Dès qu'une fiche employé est créée, activer automatiquement le compte user :
     * - email_verified_at si null (sinon bloqué par le middleware 'verified')
     * - rôle hr_employee si aucun rôle RH supérieur n'est déjà assigné
     */
    protected static function booted(): void
    {
        static::created(function (Employee $employee) {
            $user = User::find($employee->user_id);
            if (!$user) {
                return;
            }

            if (!$user->email_verified_at) {
                User::where('id', $user->id)->update(['email_verified_at' => now()]);
            }

            // N'assigne hr_employee que si aucun rôle plus élevé n'existe déjà
            if (!$user->hasAnyRole(['super_admin', 'hr_manager', 'responsable', 'hr_employee'])) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => 'hr_employee', 'guard_name' => 'web']
                );
                $user->assignRole($role);
            }
        });
    }

    protected $fillable = [
        'user_id', 'department_id', 'position_id', 'employee_code',
        'hire_date', 'contract_type', 'status', 'photo_url',
        'weekly_hours_minutes', 'max_daily_minutes', 'min_rest_minutes',
    ];

    protected function casts(): array
    {
        return ['hire_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'hr_employee_skill')
            ->withPivot('level');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scheduleOverrides(): HasMany
    {
        return $this->hasMany(EmployeeScheduleOverride::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
