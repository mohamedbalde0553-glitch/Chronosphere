<?php

namespace App\Modules\Shifts\Services;

use App\Models\User;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Shift;
use App\Notifications\ShiftAssigned;

class ShiftService
{
    public function createShift(array $data, bool $force = false): array
    {
        $conflicts = $this->detectConflicts(null, $data['employee_id'], $data['start_at'], $data['end_at']);
        if ($conflicts && !$force) {
            return ['conflicts' => $conflicts, 'status' => 409];
        }

        $workedMin   = $this->minutesBetween($data['start_at'], $data['end_at']);
        $overtimeMin = $this->computeOvertime($data['employee_id'], $data['start_at'], $workedMin, null);
        $warnings    = [];

        $weekWarning = $this->checkWeeklyLimit($data['employee_id'], $data['start_at'], $workedMin, null);
        if ($weekWarning) {
            $warnings[] = $weekWarning;
        }

        $shift = Shift::create($data + [
            'worked_minutes'   => $workedMin,
            'overtime_minutes' => $overtimeMin,
            'status'           => 'planned',
        ]);

        $shift->load(['employee.user', 'shiftType']);

        // Notify the employee
        $employee = $shift->employee;
        if ($employee?->user) {
            $employee->user->notify(new ShiftAssigned($shift));
        }

        $response = [
            'id'              => $shift->id,
            'title'           => $employee->user->name . ($shift->shiftType ? ' — ' . $shift->shiftType->name : ''),
            'start'           => $shift->start_at->toIso8601String(),
            'end'             => $shift->end_at->toIso8601String(),
            'backgroundColor' => $shift->shiftType?->color ?? '#059669',
            'borderColor'     => $shift->shiftType?->color ?? '#059669',
        ];

        if ($warnings) {
            $response['warnings'] = $warnings;
        }

        return ['data' => $response, 'status' => 201];
    }

    public function updateShift(Shift $shift, array $data, bool $force = false): array
    {
        if (isset($data['start_at'], $data['end_at'])) {
            $empId     = $data['employee_id'] ?? $shift->employee_id;
            $conflicts = $this->detectConflicts($shift->id, $empId, $data['start_at'], $data['end_at']);
            if ($conflicts && !$force) {
                return ['conflicts' => $conflicts, 'status' => 409];
            }
            $workedMin                 = $this->minutesBetween($data['start_at'], $data['end_at']);
            $data['worked_minutes']    = $workedMin;
            $data['overtime_minutes']  = $this->computeOvertime($empId, $data['start_at'], $workedMin, $shift->id);
        }

        $shift->update($data);

        return ['data' => ['ok' => true], 'status' => 200];
    }

    public function detectConflicts(?int $excludeId, int $employeeId, string $start, string $end): array
    {
        $query = Shift::where('employee_id', $employeeId)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->where('status', '!=', 'cancelled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists()
            ? [['type' => 'overlap', 'message' => "L'employé a déjà un shift sur ce créneau."]]
            : [];
    }

    public function computeOvertime(int $employeeId, string $startAt, int $newMinutes, ?int $excludeId): int
    {
        $employee = Employee::find($employeeId);
        if (!$employee) return 0;

        $dayStart = date('Y-m-d 00:00:00', strtotime($startAt));
        $dayEnd   = date('Y-m-d 23:59:59', strtotime($startAt));

        $query = Shift::where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$dayStart, $dayEnd]);

        if ($excludeId) $query->where('id', '!=', $excludeId);

        $existingDayMin = (int) $query->sum('worked_minutes');
        $totalDayMin    = $existingDayMin + $newMinutes;
        $maxDaily       = $employee->max_daily_minutes ?: 600;

        return max(0, $totalDayMin - $maxDaily);
    }

    public function checkWeeklyLimit(int $employeeId, string $startAt, int $newMinutes, ?int $excludeId): ?array
    {
        $employee = Employee::find($employeeId);
        if (!$employee) return null;

        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week', strtotime($startAt)));
        $weekEnd   = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($startAt)));

        $query = Shift::where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '>=', $weekStart)
            ->where('start_at', '<=', $weekEnd);

        if ($excludeId) $query->where('id', '!=', $excludeId);

        $weekTotal = (int) $query->sum('worked_minutes') + $newMinutes;
        $weekLimit = $employee->weekly_hours_minutes ?: 2400;

        if ($weekTotal > $weekLimit) {
            $over = $weekTotal - $weekLimit;
            return [
                'type'    => 'weekly_limit',
                'message' => sprintf(
                    "Plafond hebdo dépassé de %dh%02d (total: %dh%02d / limite: %dh%02d).",
                    intdiv($over, 60), $over % 60,
                    intdiv($weekTotal, 60), $weekTotal % 60,
                    intdiv($weekLimit, 60), $weekLimit % 60
                ),
            ];
        }

        return null;
    }

    private function minutesBetween(string $start, string $end): int
    {
        return (int) round((strtotime($end) - strtotime($start)) / 60);
    }
}
