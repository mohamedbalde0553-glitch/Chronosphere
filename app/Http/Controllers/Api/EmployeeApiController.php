<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EmployeeResource;
use App\Http\Resources\Api\LeaveRequestResource;
use App\Http\Resources\Api\ShiftResource;
use App\Modules\Shifts\Models\Employee;
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
}
