<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;

class EmployeePolicy
{
    private ?int $cachedDeptId = null;
    private bool $deptResolved = false;

    public function before(User $user): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['hr_manager', 'hr_employee', 'responsable']);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }
        if ($user->hasRole('responsable')) {
            $deptId = $this->responsableDeptId($user);
            return $deptId !== null && $employee->department_id === $deptId;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['hr_manager', 'responsable']);
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }
        if ($user->hasRole('responsable')) {
            $deptId = $this->responsableDeptId($user);
            return $deptId !== null && $employee->department_id === $deptId;
        }

        return false;
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
        if ($user->hasRole('responsable')) {
            $deptId = $this->responsableDeptId($user);
            return $deptId !== null && $employee->department_id === $deptId;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }

    public function viewLeaveRequests(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }
        if ($user->hasRole('responsable')) {
            $deptId = $this->responsableDeptId($user);
            return $deptId !== null && $employee->department_id === $deptId;
        }

        return $user->hasRole('hr_employee') && $employee->user_id === $user->id;
    }

    public function validateLeave(User $user, LeaveRequest $leave): bool
    {
        if ($user->hasRole('hr_manager')) {
            return true;
        }
        if ($user->hasRole('responsable')) {
            $deptId = $this->responsableDeptId($user);
            $leave->loadMissing('employee');
            return $deptId !== null && $leave->employee->department_id === $deptId;
        }

        return false;
    }

    /**
     * Résout l'ID du département géré par ce responsable (résultat mis en cache pour la requête).
     */
    private function responsableDeptId(User $user): ?int
    {
        if (!$this->deptResolved) {
            $this->deptResolved = true;
            $empId = Employee::where('user_id', $user->id)->value('id');
            $this->cachedDeptId = $empId
                ? Department::where('manager_id', $empId)->value('id')
                : null;
        }

        return $this->cachedDeptId;
    }
}
