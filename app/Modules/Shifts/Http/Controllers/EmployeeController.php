<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\WorkSchedule;
use App\Modules\Shifts\Services\WorkScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private WorkScheduleService $scheduleService) {}

    public function index(): View
    {
        $deptScope = $this->getManagedDepartmentId();

        $query = Employee::with(['user', 'department', 'position'])->orderBy('id');
        if ($deptScope !== null) {
            $query->where('department_id', $deptScope);
        }
        $employees = $query->paginate(20);

        $departments = $deptScope !== null
            ? Department::where('id', $deptScope)->get()
            : Department::orderBy('name')->get();

        $positions   = Position::orderBy('title')->get();
        $users       = User::doesntHave('employee')
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);

        return view('modules.shifts.employees.index', compact('employees', 'departments', 'positions', 'users'));
    }

    public function show(Employee $employee): View
    {
        $deptScope = $this->getManagedDepartmentId();
        if ($deptScope !== null && $employee->department_id !== $deptScope) {
            abort(403, 'Accès limité à votre département.');
        }

        $employee->load(['user', 'department', 'position', 'skills']);

        $leaves = $employee->leaveRequests()
            ->orderByDesc('start_date')
            ->limit(20)
            ->get();

        $today          = now()->toDateString();
        $activeSchedule = $this->findScheduleForEmployee($employee, $today);
        $expectedMinutes = $activeSchedule
            ? $this->scheduleService->calculateExpectedHours(
                $employee->id,
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            )
            : 0;

        $monthWorked = $employee->shifts()
            ->whereMonth('start_at', now()->month)
            ->whereYear('start_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('worked_minutes');

        return view('modules.shifts.employees.show', compact(
            'employee', 'leaves', 'activeSchedule', 'expectedMinutes', 'monthWorked'
        ));
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

        $deptScope = $this->getManagedDepartmentId();
        if ($deptScope !== null && (int) $data['department_id'] !== $deptScope) {
            abort(403, 'Vous ne pouvez créer des employés que dans votre département.');
        }

        $data['status'] = 'active';
        $employee = Employee::create($data);
        $employee->load(['user', 'department', 'position']);

        return response()->json($employee, 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $deptScope = $this->getManagedDepartmentId();
        if ($deptScope !== null && $employee->department_id !== $deptScope) {
            abort(403, 'Accès limité à votre département.');
        }

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
        // Seul hr_manager peut supprimer un employé
        if (!auth()->user()->can('shifts.manage_employees')) {
            abort(403, 'Suppression réservée au responsable RH.');
        }

        $employee->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Retourne l'ID du département géré par l'utilisateur courant (rôle responsable).
     * Retourne null si aucune restriction de département (hr_manager / super_admin).
     * Retourne 0 si le responsable n'a pas de département assigné.
     */
    private function getManagedDepartmentId(): ?int
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['hr_manager', 'super_admin'])) {
            return null;
        }

        $empId = Employee::where('user_id', $user->id)->value('id');
        if (!$empId) {
            return 0;
        }

        return Department::where('manager_id', $empId)->value('id') ?? 0;
    }

    private function findScheduleForEmployee(Employee $employee, string $date): ?WorkSchedule
    {
        $override = $employee->scheduleOverrides()
            ->where('override_start_date', '<=', $date)
            ->where('override_end_date', '>=', $date)
            ->first();

        if ($override) {
            return WorkSchedule::with('days')->find($override->work_schedule_id);
        }

        return WorkSchedule::with('days')
            ->active()
            ->forDate($date)
            ->where('department_id', $employee->department_id)
            ->first();
    }
}
