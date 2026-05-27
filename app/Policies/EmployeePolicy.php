<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Shifts\Models\Employee;

class EmployeePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['hr_manager', 'hr_employee']);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('hr_manager');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole('hr_manager');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole('hr_manager');
    }

    public function viewShifts(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }

    public function viewLeaveRequests(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }
}
