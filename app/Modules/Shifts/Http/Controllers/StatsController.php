<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Models\WorkSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $user    = auth()->user();
        $deptId  = $this->resolveScope($user);

        $weekStart = now()->startOfWeek()->toDateTimeString();
        $weekEnd   = now()->endOfWeek()->toDateTimeString();

        // Agrégats SQL — évite de charger tous les shifts en mémoire
        $baseQuery = Shift::where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$weekStart, $weekEnd]);

        if ($deptId) {
            $baseQuery->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }

        $agg = (clone $baseQuery)->selectRaw(
            'SUM(worked_minutes) as total_minutes, SUM(overtime_minutes) as overtime_minutes'
        )->first();

        $totalWeekMin     = (int) ($agg->total_minutes ?? 0);
        $totalOvertimeMin = (int) ($agg->overtime_minutes ?? 0);

        // Employés actifs (scope département si responsable)
        $totalEmployees = Employee::active()
            ->when($deptId, fn ($q) => $q->where('department_id', $deptId))
            ->count();

        // Congés en attente (scopé)
        $leavesPending = LeaveRequest::pending()
            ->when($deptId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $deptId)))
            ->count();

        // Taux absentéisme semaine
        $leavesApprovedWeek = LeaveRequest::approved()
            ->where('start_date', '<=', now()->endOfWeek()->toDateString())
            ->where('end_date',   '>=', now()->startOfWeek()->toDateString())
            ->when($deptId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $deptId)))
            ->count();

        $absenteeismRate = $totalEmployees > 0
            ? round($leavesApprovedWeek / ($totalEmployees * 5) * 100, 1)
            : 0;

        // Top 5 employés — agrégat SQL
        $top5 = DB::table('hr_shifts')
            ->join('hr_employees', 'hr_shifts.employee_id', '=', 'hr_employees.id')
            ->join('users', 'hr_employees.user_id', '=', 'users.id')
            ->where('hr_shifts.status', '!=', 'cancelled')
            ->whereBetween('hr_shifts.start_at', [$weekStart, $weekEnd])
            ->whereNull('hr_shifts.deleted_at')
            ->whereNull('hr_employees.deleted_at')
            ->when($deptId, fn ($q) => $q->where('hr_employees.department_id', $deptId))
            ->groupBy('hr_shifts.employee_id', 'users.name')
            ->selectRaw('users.name, SUM(hr_shifts.worked_minutes) as minutes')
            ->orderByDesc('minutes')
            ->limit(5)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'minutes' => (int) $r->minutes]);

        // Heures par département — agrégat SQL
        $byDept = DB::table('hr_shifts')
            ->join('hr_employees', 'hr_shifts.employee_id', '=', 'hr_employees.id')
            ->join('hr_departments', 'hr_employees.department_id', '=', 'hr_departments.id')
            ->where('hr_shifts.status', '!=', 'cancelled')
            ->whereBetween('hr_shifts.start_at', [$weekStart, $weekEnd])
            ->whereNull('hr_shifts.deleted_at')
            ->whereNull('hr_employees.deleted_at')
            ->when($deptId, fn ($q) => $q->where('hr_employees.department_id', $deptId))
            ->groupBy('hr_departments.name')
            ->selectRaw('hr_departments.name, ROUND(SUM(hr_shifts.worked_minutes)/60, 1) as hours')
            ->orderByDesc('hours')
            ->pluck('hours', 'name');

        // Répartition shifts par statut (scopé)
        $shiftsByStatus = DB::table('hr_shifts')
            ->whereNull('deleted_at')
            ->when($deptId, fn ($q) => $q->whereIn(
                'employee_id',
                Employee::where('department_id', $deptId)->select('id')
            ))
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return response()->json([
            'week' => [
                'total_minutes'     => $totalWeekMin,
                'overtime_minutes'  => $totalOvertimeMin,
                'leaves_pending'    => $leavesPending,
                'absenteeism_rate'  => $absenteeismRate,
                'employees_active'  => $totalEmployees,
                'departments'       => $deptId
                    ? 1
                    : Department::count(),
                'schedules_active'  => WorkSchedule::active()->forDate(now()->toDateString())
                    ->when($deptId, fn ($q) => $q->where('department_id', $deptId))
                    ->count(),
                'dept_name'         => $deptId
                    ? Department::find($deptId)?->name
                    : null,
            ],
            'top5_employees' => $top5,
            'hours_by_dept'  => $byDept,
            'shifts_status'  => $shiftsByStatus,
        ]);
    }

    public function exportExcel(): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        $shifts = Shift::with(['employee.user', 'employee.department', 'employee.position', 'shiftType'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$weekStart->toDateTimeString(), $weekEnd->toDateTimeString()])
            ->orderBy('start_at')
            ->get();

        $rows = collect([['Nom', 'Code', 'Département', 'Poste', 'Type shift', 'Début', 'Fin', 'Heures travaillées', 'Heures sup', 'Statut']]);
        foreach ($shifts as $s) {
            $rows->push([
                $s->employee->user->name,
                $s->employee->employee_code,
                $s->employee->department?->name ?? '—',
                $s->employee->position?->title ?? '—',
                $s->shiftType?->name ?? '—',
                $s->start_at->format('d/m/Y H:i'),
                $s->end_at->format('d/m/Y H:i'),
                round($s->worked_minutes / 60, 2),
                round($s->overtime_minutes / 60, 2),
                $s->status,
            ]);
        }

        $filename = 'planning_paie_' . $weekStart->format('Ymd') . '_' . $weekEnd->format('Ymd') . '.xlsx';

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private \Illuminate\Support\Collection $rows) {}
            public function collection() { return $this->rows->skip(1); }
            public function headings(): array { return $this->rows->first(); }
        }, $filename);
    }

    public function pdfData(): JsonResponse
    {
        $weekStart = now()->startOfWeek()->toDateTimeString();
        $weekEnd   = now()->endOfWeek()->toDateTimeString();

        $shifts = Shift::with(['employee.user', 'shiftType'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->orderBy('start_at')
            ->get()
            ->map(fn ($s) => [
                'employee' => $s->employee->user->name,
                'type'     => $s->shiftType?->name ?? '—',
                'start'    => $s->start_at->format('d/m H:i'),
                'end'      => $s->end_at->format('d/m H:i'),
                'hours'    => round($s->worked_minutes / 60, 1),
            ]);

        return response()->json(['shifts' => $shifts, 'week' => now()->startOfWeek()->format('d/m/Y')]);
    }

    /**
     * Retourne le department_id à filtrer pour un responsable, null pour un manager/admin.
     */
    private function resolveScope($user): ?int
    {
        if ($user->hasAnyRole(['hr_manager', 'super_admin'])) {
            return null;
        }
        if ($user->hasRole('responsable')) {
            $empId = Employee::where('user_id', $user->id)->value('id');
            return $empId
                ? Department::where('manager_id', $empId)->value('id')
                : null;
        }
        return null;
    }
}
