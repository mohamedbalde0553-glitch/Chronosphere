<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EmployeeResource;
use App\Http\Resources\Api\LeaveRequestResource;
use App\Http\Resources\Api\ShiftResource;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::with(['user', 'department', 'position']);

        // hr_employee ne voit que sa propre fiche
        if ($request->user()->hasRole('hr_employee')) {
            $query->where('user_id', $request->user()->id);
        } else {
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            }
        }

        return EmployeeResource::collection(
            $query->orderBy('id')->paginate($request->integer('per_page', 20))
        );
    }

    public function show(Employee $employee): EmployeeResource
    {
        $this->authorize('view', $employee);

        $employee->load(['user', 'department', 'position', 'skills']);

        return new EmployeeResource($employee);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

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

        return (new EmployeeResource($employee))->response()->setStatusCode(201);
    }

    public function update(Request $request, Employee $employee): EmployeeResource
    {
        $this->authorize('update', $employee);

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

        return new EmployeeResource($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json(['message' => 'Employé supprimé.']);
    }

    public function shifts(Request $request, Employee $employee): AnonymousResourceCollection
    {
        $this->authorize('viewShifts', $employee);

        $query = $employee->shifts()->with('shiftType')->orderByDesc('start_at');

        if ($request->filled('from') && $request->filled('to')) {
            $query->inRange($request->from, $request->to);
        }

        return ShiftResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function leaveRequests(Request $request, Employee $employee): AnonymousResourceCollection
    {
        $this->authorize('viewLeaveRequests', $employee);

        $query = $employee->leaveRequests()->with('validator')->orderByDesc('start_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return LeaveRequestResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function storeLeaveRequest(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('viewLeaveRequests', $employee);

        $user = $request->user();
        if ($user->hasRole('hr_employee')) {
            $myEmployee = Employee::where('user_id', $user->id)->firstOrFail();
            if ($myEmployee->id !== $employee->id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'leave_type' => 'required|in:conge_paye,maladie,sans_solde,autre',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:500',
        ]);

        $leave = $employee->leaveRequests()->create([
            ...$data,
            'status' => 'pending',
        ]);

        return (new LeaveRequestResource($leave))->response()->setStatusCode(201);
    }

    public function approveLeaveRequest(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('validateLeave', $leaveRequest);

        $leaveRequest->update([
            'status'       => 'approved',
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);

        return response()->json(['message' => 'Congé approuvé.', 'data' => new LeaveRequestResource($leaveRequest)]);
    }

    public function rejectLeaveRequest(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('validateLeave', $leaveRequest);

        $request->validate(['reason' => 'required|string|max:255']);

        $leaveRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'validated_by'     => $request->user()->id,
            'validated_at'     => now(),
        ]);

        return response()->json(['message' => 'Congé refusé.', 'data' => new LeaveRequestResource($leaveRequest)]);
    }
}
