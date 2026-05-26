<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id'   => 'required|exists:hr_employees,id',
            'shift_type_id' => 'nullable|exists:hr_shift_types,id',
            'start_at'      => 'required|date',
            'end_at'        => 'required|date|after:start_at',
            'notes'         => 'nullable|string',
        ]);

        $warnings = [];

        $conflicts = $this->detectConflicts(null, $data['employee_id'], $data['start_at'], $data['end_at']);
        if ($conflicts && !$request->boolean('force')) {
            return response()->json(['conflicts' => $conflicts], 409);
        }

        $workedMin  = (int) round((strtotime($data['end_at']) - strtotime($data['start_at'])) / 60);
        $overtimeMin = $this->computeOvertime($data['employee_id'], $data['start_at'], $workedMin, null);

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

        $response = [
            'id'              => $shift->id,
            'title'           => $shift->employee->user->name
                                 . ($shift->shiftType ? ' — ' . $shift->shiftType->name : ''),
            'start'           => $shift->start_at->toIso8601String(),
            'end'             => $shift->end_at->toIso8601String(),
            'backgroundColor' => $shift->shiftType?->color ?? '#059669',
            'borderColor'     => $shift->shiftType?->color ?? '#059669',
        ];

        if ($warnings) {
            $response['warnings'] = $warnings;
        }

        return response()->json($response, 201);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $data = $request->validate([
            'start_at'      => 'sometimes|required|date',
            'end_at'        => 'sometimes|required|date|after:start_at',
            'employee_id'   => 'sometimes|required|exists:hr_employees,id',
            'shift_type_id' => 'nullable|exists:hr_shift_types,id',
            'status'        => 'nullable|string|in:planned,in_progress,completed,cancelled',
            'notes'         => 'nullable|string',
        ]);

        if (isset($data['start_at'], $data['end_at'])) {
            $empId = $data['employee_id'] ?? $shift->employee_id;
            $conflicts = $this->detectConflicts($shift->id, $empId, $data['start_at'], $data['end_at']);
            if ($conflicts && !$request->boolean('force')) {
                return response()->json(['conflicts' => $conflicts], 409);
            }
            $workedMin  = (int) round((strtotime($data['end_at']) - strtotime($data['start_at'])) / 60);
            $data['worked_minutes']   = $workedMin;
            $data['overtime_minutes'] = $this->computeOvertime($empId, $data['start_at'], $workedMin, $shift->id);
        }

        $shift->update($data);
        return response()->json(['ok' => true]);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->delete();
        return response()->json(['ok' => true]);
    }

    private function detectConflicts(?int $excludeId, int $employeeId, string $start, string $end): array
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

    private function computeOvertime(int $employeeId, string $startAt, int $newMinutes, ?int $excludeId): int
    {
        $employee = Employee::find($employeeId);
        if (!$employee) return 0;

        $dayStart = date('Y-m-d 00:00:00', strtotime($startAt));
        $dayEnd   = date('Y-m-d 23:59:59', strtotime($startAt));

        $query = Shift::where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$dayStart, $dayEnd]);

        if ($excludeId) $query->where('id', '!=', $excludeId);

        $existingDayMin  = (int) $query->sum('worked_minutes');
        $totalDayMin     = $existingDayMin + $newMinutes;
        $maxDaily        = $employee->max_daily_minutes ?: 600;

        return max(0, $totalDayMin - $maxDaily);
    }

    private function checkWeeklyLimit(int $employeeId, string $startAt, int $newMinutes, ?int $excludeId): ?array
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

        $weekTotal  = (int) $query->sum('worked_minutes') + $newMinutes;
        $weekLimit  = $employee->weekly_hours_minutes ?: 2400;

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
}
