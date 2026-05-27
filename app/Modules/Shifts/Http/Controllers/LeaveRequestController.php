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
        $leaves    = LeaveRequest::with(['employee.user', 'validator'])
                        ->orderByDesc('created_at')
                        ->paginate(20);
        $employees = Employee::with('user')->active()->get();

        return view('modules.shifts.leaves.index', compact('leaves', 'employees'));
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $data           = $request->validated();
        $data['status'] = 'pending';

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
