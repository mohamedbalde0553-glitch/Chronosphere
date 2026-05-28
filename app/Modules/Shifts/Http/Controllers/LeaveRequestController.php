<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Http\Requests\StoreLeaveRequest;
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

        $query = LeaveRequest::with(['employee.user', 'validator'])->orderByDesc('created_at');

        // hr_employee ne voit que ses propres congés
        if ($user->hasRole('hr_employee') && $employee) {
            $query->where('employee_id', $employee->id);
        }

        $leaves    = $query->paginate(20);
        $employees = $user->hasRole('hr_employee')
            ? collect([$employee])->filter()
            : Employee::with('user')->active()->get();

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
        $leave->delete();
        return response()->json(['ok' => true]);
    }

    public function approve(LeaveRequest $leave): JsonResponse
    {
        $result = $this->leaveService->approve($leave, auth()->id());
        return response()->json($result);
    }

    public function reject(Request $request, LeaveRequest $leave): JsonResponse
    {
        $data   = $request->validate(['rejection_reason' => 'nullable|string']);
        $result = $this->leaveService->reject($leave, auth()->id(), $data['rejection_reason'] ?? null);
        return response()->json($result);
    }
}
