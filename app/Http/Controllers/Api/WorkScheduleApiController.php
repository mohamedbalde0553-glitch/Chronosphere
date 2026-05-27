<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EmployeeResource;
use App\Http\Resources\Api\WorkScheduleResource;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\EmployeeScheduleOverride;
use App\Modules\Shifts\Models\WorkSchedule;
use App\Modules\Shifts\Services\WorkScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkScheduleApiController extends Controller
{
    public function __construct(private WorkScheduleService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Employee::class);

        $query = WorkSchedule::with(['department', 'days']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }

        return WorkScheduleResource::collection(
            $query->orderByDesc('start_date')->paginate($request->integer('per_page', 20))
        );
    }

    public function show(WorkSchedule $work_schedule): WorkScheduleResource
    {
        $this->authorize('viewAny', Employee::class);

        $work_schedule->load(['department', 'days', 'creator']);

        return new WorkScheduleResource($work_schedule);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

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

        $data['created_by'] = $request->user()->id;
        $days = $data['days'];
        unset($data['days']);

        $schedule = WorkSchedule::create($data);
        foreach ($days as $day) {
            $schedule->days()->create($day);
        }

        $schedule->load(['department', 'days']);
        return (new WorkScheduleResource($schedule))->response()->setStatusCode(201);
    }

    public function update(Request $request, WorkSchedule $work_schedule): WorkScheduleResource
    {
        $this->authorize('create', Employee::class);

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

        $work_schedule->update($data);
        $work_schedule->days()->delete();
        foreach ($days as $day) {
            $work_schedule->days()->create($day);
        }

        $work_schedule->load(['department', 'days']);
        return new WorkScheduleResource($work_schedule);
    }

    public function destroy(WorkSchedule $work_schedule): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $work_schedule->delete();
        return response()->json(['message' => 'Horaire supprimé.']);
    }

    public function generateShifts(Request $request, WorkSchedule $work_schedule): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $count = $this->service->generateShiftsFromSchedule(
            $work_schedule->id,
            $data['start_date'],
            $data['end_date']
        );

        return response()->json(['created' => $count]);
    }

    public function employeeSchedule(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $date     = $request->get('date', now()->toDateString());
        $schedule = $this->findScheduleForEmployee($employee, $date);

        if (! $schedule) {
            return response()->json(['schedule' => null, 'expected_minutes' => 0]);
        }

        $from            = $request->get('from', now()->startOfMonth()->toDateString());
        $to              = $request->get('to', now()->endOfMonth()->toDateString());
        $expectedMinutes = $this->service->calculateExpectedHours($employee->id, $from, $to);

        return response()->json([
            'schedule'         => new WorkScheduleResource($schedule->load(['days', 'department'])),
            'expected_minutes' => $expectedMinutes,
        ]);
    }

    public function storeOverride(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validate([
            'work_schedule_id'    => 'required|exists:hr_work_schedules,id',
            'override_start_date' => 'required|date',
            'override_end_date'   => 'required|date|after_or_equal:override_start_date',
            'reason'              => 'nullable|string|max:500',
        ]);

        $data['employee_id'] = $employee->id;
        $override = EmployeeScheduleOverride::create($data);
        $override->load('schedule.days');

        return response()->json($override, 201);
    }

    private function findScheduleForEmployee(Employee $employee, string $date): ?WorkSchedule
    {
        $override = $employee->scheduleOverrides()
            ->where('override_start_date', '<=', $date)
            ->where('override_end_date', '>=', $date)
            ->first();

        if ($override) {
            return WorkSchedule::find($override->work_schedule_id);
        }

        return WorkSchedule::active()
            ->forDate($date)
            ->where('department_id', $employee->department_id)
            ->first();
    }
}
