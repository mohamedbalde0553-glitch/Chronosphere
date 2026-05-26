<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
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

        $conflicts = $this->detectConflicts(null, $data['employee_id'], $data['start_at'], $data['end_at']);
        if ($conflicts && !$request->boolean('force')) {
            return response()->json(['conflicts' => $conflicts], 409);
        }

        $shift = Shift::create($data + [
            'worked_minutes' => (int) round((strtotime($data['end_at']) - strtotime($data['start_at'])) / 60),
            'status'         => 'planned',
        ]);

        $shift->load(['employee.user', 'shiftType']);

        return response()->json([
            'id'              => $shift->id,
            'title'           => $shift->employee->user->name
                                 . ($shift->shiftType ? ' — ' . $shift->shiftType->name : ''),
            'start'           => $shift->start_at->toIso8601String(),
            'end'             => $shift->end_at->toIso8601String(),
            'backgroundColor' => $shift->shiftType?->color ?? '#059669',
            'borderColor'     => $shift->shiftType?->color ?? '#059669',
        ], 201);
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
            $data['worked_minutes'] = (int) round(
                (strtotime($data['end_at']) - strtotime($data['start_at'])) / 60
            );
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

        if ($query->exists()) {
            return [['type' => 'overlap', 'message' => "L'employé a déjà un shift sur ce créneau."]];
        }

        return [];
    }
}
