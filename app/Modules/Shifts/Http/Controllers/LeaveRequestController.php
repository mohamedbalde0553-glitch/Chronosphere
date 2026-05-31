<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Http\Requests\StoreLeaveRequest;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveRequestService $leaveService) {}

    public function index(): View
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        $deptScope = $this->getManagedDepartmentId($user, $employee);

        $query = LeaveRequest::with(['employee.user', 'validator'])->orderByDesc('created_at');

        if ($user->hasRole('hr_employee') && $employee) {
            $query->where('employee_id', $employee->id);
            $employees = collect([$employee])->filter();
        } elseif ($deptScope !== null) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptScope));
            $employees = Employee::where('department_id', $deptScope)
                ->select('id', 'user_id')
                ->with(['user:id,name'])
                ->get();
        } else {
            $employees = Employee::active()
                ->select('id', 'user_id')
                ->with(['user:id,name'])
                ->get();
        }

        $leaves = $query->paginate(20);

        return view('modules.shifts.leaves.index', compact('leaves', 'employees'));
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $data           = $request->validated();
        $data['status'] = 'pending';

        // hr_employee ne peut soumettre que pour lui-même
        if ($user->hasRole('hr_employee') && $employee) {
            $data['employee_id'] = $employee->id;
        }

        return response()->json(LeaveRequest::create($data), 201);
    }

    public function update(Request $request, LeaveRequest $leave): JsonResponse
    {
        $this->authorizeLeaveAccess($leave);

        $data = $request->validate([
            'type'       => 'required|string|in:conge_paye,rtt,maladie,sans_solde,autre',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string',
        ]);

        $leave->update($data);
        return response()->json($leave);
    }

    public function destroy(LeaveRequest $leave): JsonResponse
    {
        $this->authorizeLeaveAccess($leave);
        $leave->delete();
        return response()->json(['ok' => true]);
    }

    public function approve(LeaveRequest $leave): JsonResponse
    {
        $this->authorizeLeaveAccess($leave);
        $result = $this->leaveService->approve($leave, auth()->id());
        return response()->json($result);
    }

    public function reject(Request $request, LeaveRequest $leave): JsonResponse
    {
        $this->authorizeLeaveAccess($leave);
        $data   = $request->validate(['rejection_reason' => 'nullable|string']);
        $result = $this->leaveService->reject($leave, auth()->id(), $data['rejection_reason'] ?? null);
        return response()->json($result);
    }

    /**
     * Vérifie que l'utilisateur peut agir sur ce congé (hr_manager : tous ; responsable : son dept).
     */
    private function authorizeLeaveAccess(LeaveRequest $leave): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['hr_manager', 'super_admin'])) {
            return;
        }
        if ($user->hasRole('responsable')) {
            $employee  = Employee::where('user_id', $user->id)->value('id');
            $deptId    = $employee ? Department::where('manager_id', $employee)->value('id') : null;
            $leave->loadMissing('employee');
            if ($deptId && $leave->employee->department_id === $deptId) {
                return;
            }
        }
        abort(403, 'Accès refusé.');
    }

    /**
     * Retourne l'ID du département géré (rôle responsable) ou null si accès total.
     */
    private function getManagedDepartmentId($user, ?Employee $currentEmployee): ?int
    {
        if ($user->hasAnyRole(['hr_manager', 'super_admin'])) {
            return null;
        }
        if (!$user->hasRole('responsable')) {
            return null;
        }

        $empId = $currentEmployee?->id ?? Employee::where('user_id', $user->id)->value('id');
        if (!$empId) {
            return 0;
        }

        return Department::where('manager_id', $empId)->value('id') ?? 0;
    }
}
