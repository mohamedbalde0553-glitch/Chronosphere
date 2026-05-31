<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Models\ShiftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftsController extends Controller
{
    public function index(): View
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        // hr_employee : tableau de bord personnel (avec ou sans fiche)
        if ($user->hasRole('hr_employee')) {
            if (!$employee) {
                // Compte hr_employee sans fiche employé — dashboard vide sécurisé
                return view('modules.shifts.employee_dashboard', [
                    'employee'       => null,
                    'stats'          => ['shifts_week' => 0, 'shifts_month' => 0, 'leaves_pending' => 0, 'leaves_approved' => 0],
                    'upcomingShifts' => collect(),
                    'myLeaves'       => collect(),
                ]);
            }
            $startWeek = now()->startOfWeek()->toDateTimeString();
            $endWeek   = now()->endOfWeek()->toDateTimeString();

            $stats = [
                'shifts_week'    => Shift::where('employee_id', $employee->id)->inRange($startWeek, $endWeek)->count(),
                'shifts_month'   => Shift::where('employee_id', $employee->id)
                                        ->inRange(now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString())
                                        ->count(),
                'leaves_pending' => LeaveRequest::where('employee_id', $employee->id)->pending()->count(),
                'leaves_approved'=> LeaveRequest::where('employee_id', $employee->id)->approved()
                                        ->whereYear('start_date', now()->year)->count(),
            ];

            $upcomingShifts = Shift::with(['shiftType'])
                ->where('employee_id', $employee->id)
                ->where('start_at', '>=', now())
                ->where('start_at', '<=', now()->addDays(14))
                ->where('status', 'planned')
                ->orderBy('start_at')
                ->limit(10)
                ->get();

            $myLeaves = LeaveRequest::where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return view('modules.shifts.employee_dashboard', compact(
                'employee', 'stats', 'upcomingShifts', 'myLeaves'
            ));
        }

        // Vue manager / admin
        $stats = [
            'employees'      => Employee::active()->count(),
            'departments'    => Department::count(),
            'shifts_week'    => Shift::inRange(
                                    now()->startOfWeek()->toDateTimeString(),
                                    now()->endOfWeek()->toDateTimeString()
                                )->count(),
            'leaves_pending' => LeaveRequest::pending()->count(),
        ];

        $upcomingShifts = Shift::with(['employee.user', 'shiftType'])
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addDays(7))
            ->where('status', 'planned')
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $pendingLeaves = LeaveRequest::with(['employee.user'])
            ->pending()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('modules.shifts.index', compact('stats', 'upcomingShifts', 'pendingLeaves'));
    }

    public function planning(Request $request): View
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $shiftTypes = ShiftType::orderBy('name')->get(['id', 'name', 'color']);

        // hr_employee : vue personnelle uniquement — pas besoin de charger tous les employés
        if ($user->hasRole('hr_employee')) {
            $departments = collect();
            $employees   = $employee
                ? Employee::where('id', $employee->id)
                    ->select('id', 'user_id', 'department_id')
                    ->with(['user:id,name', 'department:id,name'])
                    ->get()
                : collect();
            $filterType  = 'employee';
            $filterId    = $employee?->id;
        } else {
            $departments = Department::orderBy('name')->get(['id', 'name']);
            $employees   = Employee::active()
                               ->select('id', 'user_id', 'department_id')
                               ->with(['user:id,name', 'department:id,name'])
                               ->orderBy('id')
                               ->get();
            $filterType  = $request->get('by', 'department');
            $filterId    = $request->get('id', $departments->first()?->id);
        }

        return view('modules.shifts.planning', compact(
            'departments', 'employees', 'shiftTypes', 'filterType', 'filterId'
        ));
    }

    public function feed(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $start      = $request->query('start', now()->startOfWeek()->toDateTimeString());
        $end        = $request->query('end', now()->endOfWeek()->toDateTimeString());
        $filterType = $request->query('by', 'department');
        $filterId   = $request->query('id');

        // Sécurité : hr_employee ne peut voir QUE ses propres shifts
        if ($user->hasRole('hr_employee')) {
            if (!$employee) {
                return response()->json([]); // pas de fiche = aucun event
            }
            $filterType = 'employee';
            $filterId   = $employee->id;
        }

        $query = Shift::with(['employee.user', 'shiftType'])
            ->inRange($start, $end)
            ->where('status', '!=', 'cancelled');

        if ($filterId) {
            match ($filterType) {
                'department' => $query->whereHas('employee', fn ($q) => $q->where('department_id', $filterId)),
                'employee'   => $query->where('employee_id', $filterId),
                default      => null,
            };
        }

        $shifts = $query->get()->map(fn (Shift $s) => [
            'id'              => $s->id,
            'title'           => $s->employee->user->name
                                 . ($s->shiftType ? ' — ' . $s->shiftType->name : ''),
            'start'           => $s->start_at->toIso8601String(),
            'end'             => $s->end_at->toIso8601String(),
            'backgroundColor' => $s->shiftType?->color ?? '#059669',
            'borderColor'     => $s->shiftType?->color ?? '#059669',
            'extendedProps'   => [
                'employee_id'     => $s->employee_id,
                'employee_name'   => $s->employee->user->name,
                'shift_type_id'   => $s->shift_type_id,
                'shift_type_name' => $s->shiftType?->name,
                'status'          => $s->status,
                'notes'           => $s->notes,
            ],
        ]);

        $leaveQuery = LeaveRequest::with(['employee.user'])
            ->approved()
            ->where('start_date', '<=', substr($end, 0, 10))
            ->where('end_date', '>=', substr($start, 0, 10));

        if ($filterId) {
            match ($filterType) {
                'department' => $leaveQuery->whereHas('employee', fn ($q) => $q->where('department_id', $filterId)),
                'employee'   => $leaveQuery->where('employee_id', $filterId),
                default      => null,
            };
        }

        $leaves = $leaveQuery->get()->map(fn (LeaveRequest $l) => [
            'id'              => 'leave-' . $l->id,
            'title'           => $l->employee->user->name . ' — Congé',
            'start'           => $l->start_date->toDateString(),
            'end'             => $l->end_date->addDay()->toDateString(),
            'allDay'          => true,
            'backgroundColor' => '#FEF3C7',
            'borderColor'     => '#F59E0B',
            'textColor'       => '#92400E',
            'display'         => 'background',
            'extendedProps'   => ['type' => 'leave'],
        ]);

        return response()->json($shifts->merge($leaves)->values());
    }
}
