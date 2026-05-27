<?php

namespace App\Modules\Shifts\Services;

use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Shift;
use App\Notifications\LeaveRequestApproved;
use App\Notifications\LeaveRequestRejected;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    public function approve(LeaveRequest $leave, int $validatorId): array
    {
        return DB::transaction(function () use ($leave, $validatorId) {
            $leave->update([
                'status'       => 'approved',
                'validated_by' => $validatorId,
                'validated_at' => now(),
            ]);

            $cancelled = Shift::where('employee_id', $leave->employee_id)
                ->where('status', '!=', 'cancelled')
                ->where('start_at', '<', $leave->end_date->addDay()->toDateTimeString())
                ->where('end_at', '>',  $leave->start_date->toDateTimeString())
                ->update(['status' => 'cancelled']);

            // Notify employee
            $employee = $leave->employee()->with('user')->first();
            if ($employee?->user) {
                $employee->user->notify(new LeaveRequestApproved($leave));
            }

            return ['ok' => true, 'shifts_cancelled' => $cancelled];
        });
    }

    public function reject(LeaveRequest $leave, int $validatorId, ?string $reason): array
    {
        $leave->update([
            'status'           => 'rejected',
            'validated_by'     => $validatorId,
            'validated_at'     => now(),
            'rejection_reason' => $reason,
        ]);

        // Notify employee
        $employee = $leave->employee()->with('user')->first();
        if ($employee?->user) {
            $employee->user->notify(new LeaveRequestRejected($leave));
        }

        return ['ok' => true];
    }
}
