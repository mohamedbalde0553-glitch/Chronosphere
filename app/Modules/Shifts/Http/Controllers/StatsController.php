<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $weekStart = now()->startOfWeek()->toDateTimeString();
        $weekEnd   = now()->endOfWeek()->toDateTimeString();

        $weekShifts = Shift::with(['employee.user', 'employee.department'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->get();

        // Total heures semaine
        $totalWeekMin = $weekShifts->sum('worked_minutes');

        // Heures sup semaine
        $totalOvertimeMin = $weekShifts->sum('overtime_minutes');

        // Congés en attente
        $leavesPending = LeaveRequest::pending()->count();

        // Congés approuvés sur la semaine
        $leavesApprovedWeek = LeaveRequest::approved()
            ->where('start_date', '<=', now()->endOfWeek()->toDateString())
            ->where('end_date',   '>=', now()->startOfWeek()->toDateString())
            ->count();

        $totalEmployees = Employee::active()->count();

        // Taux absentéisme = (jours congé approuvé cette sem) / (employés * 5 jours)
        $absenteeismRate = $totalEmployees > 0
            ? round($leavesApprovedWeek / ($totalEmployees * 5) * 100, 1)
            : 0;

        // Top 5 employés par heures travaillées cette semaine
        $top5 = $weekShifts
            ->groupBy('employee_id')
            ->map(fn ($shifts) => [
                'name'    => $shifts->first()->employee->user->name,
                'minutes' => $shifts->sum('worked_minutes'),
            ])
            ->sortByDesc('minutes')
            ->take(5)
            ->values();

        // Heures par département (semaine)
        $byDept = $weekShifts
            ->groupBy(fn ($s) => $s->employee->department?->name ?? 'Autre')
            ->map(fn ($shifts) => round($shifts->sum('worked_minutes') / 60, 1))
            ->sortByDesc(fn ($v) => $v)
            ->all();

        // Répartition shifts par statut
        $shiftsByStatus = Shift::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return response()->json([
            'week' => [
                'total_minutes'    => $totalWeekMin,
                'overtime_minutes' => $totalOvertimeMin,
                'leaves_pending'   => $leavesPending,
                'absenteeism_rate' => $absenteeismRate,
                'employees_active' => $totalEmployees,
                'departments'      => Department::count(),
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

        $rows   = collect([['Nom', 'Code', 'Département', 'Poste', 'Type shift', 'Début', 'Fin', 'Heures travaillées', 'Heures sup', 'Statut']]);
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
}
