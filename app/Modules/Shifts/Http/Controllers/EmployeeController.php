<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees   = Employee::with(['user', 'department', 'position'])->orderBy('id')->paginate(60);
        $departments = Department::orderBy('name')->get();
        $positions   = Position::orderBy('name')->get();
        $users       = User::doesntHave('employee')->orderBy('name')->get(['id', 'name', 'email']);

        return view('modules.shifts.employees.index', compact('employees', 'departments', 'positions', 'users'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'              => 'required|exists:users,id|unique:hr_employees,user_id',
            'department_id'        => 'required|exists:hr_departments,id',
            'position_id'          => 'required|exists:hr_positions,id',
            'employee_code'        => 'required|string|max:30|unique:hr_employees,employee_code',
            'hire_date'            => 'required|date',
            'contract_type'        => 'required|string|in:cdi,cdd,interim,freelance',
            'weekly_hours_minutes' => 'required|integer|min:60|max:3600',
            'photo_url'            => 'nullable|url',
        ]);

        $data['status'] = 'active';
        $employee = Employee::create($data);
        $employee->load(['user', 'department', 'position']);

        return response()->json($employee, 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'department_id'        => 'required|exists:hr_departments,id',
            'position_id'          => 'required|exists:hr_positions,id',
            'employee_code'        => 'required|string|max:30|unique:hr_employees,employee_code,' . $employee->id,
            'hire_date'            => 'required|date',
            'contract_type'        => 'required|string|in:cdi,cdd,interim,freelance',
            'weekly_hours_minutes' => 'required|integer|min:60|max:3600',
            'status'               => 'required|string|in:active,inactive,suspended',
            'photo_url'            => 'nullable|url',
        ]);

        $employee->update($data);
        $employee->load(['user', 'department', 'position']);
        return response()->json($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(['ok' => true]);
    }
}
