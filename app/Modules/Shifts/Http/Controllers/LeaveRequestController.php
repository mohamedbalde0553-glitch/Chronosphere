<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $leaves    = LeaveRequest::with(['employee.user', 'validator'])
                        ->orderByDesc('created_at')
                        ->paginate(20);
        $employees = Employee::with('user')->active()->get();

        return view('modules.shifts.leaves.index', compact('leaves', 'employees'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id'    => 'required|exists:hr_employees,id',
            'type'           => 'required|string|in:conge_paye,rtt,maladie,sans_solde,autre',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'nullable|string',
        ]);

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
        $leave->update([
            'status'       => 'approved',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);
        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, LeaveRequest $leave): JsonResponse
    {
        $data = $request->validate(['rejection_reason' => 'nullable|string']);

        $leave->update([
            'status'           => 'rejected',
            'validated_by'     => auth()->id(),
            'validated_at'     => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);
        return response()->json(['ok' => true]);
    }
}
