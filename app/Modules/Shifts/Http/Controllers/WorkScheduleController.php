<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\EmployeeScheduleOverride;
use App\Modules\Shifts\Models\WorkSchedule;
use App\Modules\Shifts\Services\WorkScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function __construct(private WorkScheduleService $service) {}

    public function index(Request $request): View
    {
        $query = WorkSchedule::with('department')->orderByDesc('start_date');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }

        $schedules   = $query->paginate(20);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('modules.shifts.schedules.index', compact('schedules', 'departments'));
    }

    public function show(WorkSchedule $schedule): View
    {
        $schedule->load(['days', 'department', 'creator']);

        $employees = $schedule->department_id
            ? Employee::with('user')->active()->where('department_id', $schedule->department_id)->get()
            : Employee::with('user')->active()->get();

        $conflicts  = $this->service->detectConflicts($schedule->id);
        $schedules  = WorkSchedule::active()->orderBy('name')->get(['id', 'name']);
        $overrides  = $schedule->overrides()->with('employee.user')->get();

        return view('modules.shifts.schedules.show',
            compact('schedule', 'employees', 'conflicts', 'schedules', 'overrides'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:hr_departments,id',
            'color'         => 'nullable|string|max:7',
            'is_active'     => 'boolean',
            'days'          => 'required|array|min:1',
            'days.*.day_of_week'          => 'required|integer|between:0,6',
            'days.*.start_time'           => 'required|date_format:H:i',
            'days.*.end_time'             => 'required|date_format:H:i',
            'days.*.break_minutes'        => 'integer|min:0|max:480',
            'days.*.is_overtime_eligible' => 'boolean',
            'days.*.multiplier'           => 'numeric|min:1|max:3',
        ]);

        $data['created_by'] = auth()->id();
        $days = $data['days'];
        unset($data['days']);

        $schedule = WorkSchedule::create($data);
        foreach ($days as $day) {
            $schedule->days()->create($day);
        }

        $schedule->load('days');
        return response()->json($schedule, 201);
    }

    public function update(Request $request, WorkSchedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:hr_departments,id',
            'color'         => 'nullable|string|max:7',
            'is_active'     => 'boolean',
            'days'          => 'required|array|min:1',
            'days.*.day_of_week'          => 'required|integer|between:0,6',
            'days.*.start_time'           => 'required|date_format:H:i',
            'days.*.end_time'             => 'required|date_format:H:i',
            'days.*.break_minutes'        => 'integer|min:0|max:480',
            'days.*.is_overtime_eligible' => 'boolean',
            'days.*.multiplier'           => 'numeric|min:1|max:3',
        ]);

        $days = $data['days'];
        unset($data['days']);

        $schedule->update($data);
        $schedule->days()->delete();
        foreach ($days as $day) {
            $schedule->days()->create($day);
        }

        $schedule->load('days');
        return response()->json($schedule);
    }

    public function destroy(WorkSchedule $schedule): JsonResponse
    {
        $schedule->delete();
        return response()->json(['ok' => true]);
    }

    public function generateShifts(Request $request, WorkSchedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $count = $this->service->generateShiftsFromSchedule(
            $schedule->id,
            $data['start_date'],
            $data['end_date']
        );

        return response()->json(['created' => $count]);
    }

    public function storeOverride(Request $request, WorkSchedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'employee_id'         => 'required|exists:hr_employees,id',
            'override_start_date' => 'required|date',
            'override_end_date'   => 'required|date|after_or_equal:override_start_date',
            'reason'              => 'nullable|string|max:500',
        ]);

        $data['work_schedule_id'] = $schedule->id;
        $override = EmployeeScheduleOverride::create($data);
        $override->load('employee.user');

        return response()->json($override, 201);
    }

    public function destroyOverride(EmployeeScheduleOverride $override): JsonResponse
    {
        $override->delete();
        return response()->json(['ok' => true]);
    }
}
