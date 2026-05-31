<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $period    = $request->input('period', 'week');
        $deptId    = $request->input('department_id');
        [$start, $end] = $this->resolvePeriod($period, $request);

        $departments = Department::orderBy('name')->get();
        $stats       = $this->buildStats($start, $end, $deptId);

        return view('modules.shifts.rapports.index', compact(
            'stats', 'departments', 'period', 'deptId', 'start', 'end'
        ));
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');

        $period  = $request->input('period', 'week');
        $deptId  = $request->input('department_id');
        [$start, $end] = $this->resolvePeriod($period, $request);

        $stats  = $this->buildStats($start, $end, $deptId);
        $shifts = $this->buildShiftRows($start, $end, $deptId);

        $pdf = Pdf::loadView('modules.shifts.rapports.pdf', compact('stats', 'shifts', 'start', 'end', 'period'))
                  ->setPaper('a4', 'landscape')
                  ->setOption('isPhpEnabled', false)
                  ->setOption('isRemoteEnabled', false);

        $filename = 'rapport_rh_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $period  = $request->input('period', 'week');
        $deptId  = $request->input('department_id');
        [$start, $end] = $this->resolvePeriod($period, $request);

        $shifts = $this->buildShiftRows($start, $end, $deptId);

        $headers = ['Nom', 'Code', 'Département', 'Poste', 'Type shift', 'Début', 'Fin', 'Heures', 'Heures sup', 'Statut'];
        $rows    = collect([$headers]);
        foreach ($shifts as $s) {
            $rows->push([
                $s['employee'], $s['code'], $s['department'], $s['position'],
                $s['shift_type'], $s['start'], $s['end'],
                $s['hours'], $s['overtime'], $s['status'],
            ]);
        }

        $filename = 'rapport_rh_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.xlsx';

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private \Illuminate\Support\Collection $rows) {}
            public function collection() { return $this->rows->skip(1); }
            public function headings(): array { return $this->rows->first(); }
        }, $filename);
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'month'       => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month'  => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'custom'      => [
                Carbon::parse($request->input('from', now()->startOfWeek())),
                Carbon::parse($request->input('to',   now()->endOfWeek())),
            ],
            default       => [now()->startOfWeek(), now()->endOfWeek()], // week
        };
    }

    private function buildStats(Carbon $start, Carbon $end, ?int $deptId): array
    {
        $query = Shift::with(['employee.user', 'employee.department'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$start->toDateTimeString(), $end->toDateTimeString()]);

        if ($deptId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }

        $shifts = $query->get();

        $totalWorked    = $shifts->sum('worked_minutes');
        $totalOvertime  = $shifts->sum('overtime_minutes');
        $totalEmployees = Employee::active()->when($deptId, fn ($q) => $q->where('department_id', $deptId))->count();

        $leaveQuery = LeaveRequest::approved()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString());
        if ($deptId) {
            $leaveQuery->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }
        $leaveDays  = $leaveQuery->count();
        $periodDays = $start->diffInDays($end) + 1;

        $absenteeism = $totalEmployees > 0 && $periodDays > 0
            ? round($leaveDays / ($totalEmployees * $periodDays) * 100, 1)
            : 0;

        $byDept = $shifts
            ->groupBy(fn ($s) => $s->employee->department?->name ?? 'Autre')
            ->map(fn ($g) => [
                'shifts'   => $g->count(),
                'hours'    => round($g->sum('worked_minutes') / 60, 1),
                'overtime' => round($g->sum('overtime_minutes') / 60, 1),
            ])
            ->sortByDesc(fn ($v) => $v['hours'])
            ->all();

        $top5 = $shifts
            ->groupBy('employee_id')
            ->map(fn ($g) => [
                'name'     => $g->first()->employee->user->name,
                'hours'    => round($g->sum('worked_minutes') / 60, 1),
                'overtime' => round($g->sum('overtime_minutes') / 60, 1),
                'shifts'   => $g->count(),
            ])
            ->sortByDesc('hours')
            ->take(5)
            ->values()
            ->all();

        return compact('totalWorked', 'totalOvertime', 'totalEmployees', 'leaveDays', 'absenteeism', 'byDept', 'top5');
    }

    private function buildShiftRows(Carbon $start, Carbon $end, ?int $deptId): array
    {
        $query = Shift::with(['employee.user', 'employee.department', 'employee.position', 'shiftType'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->orderBy('start_at');

        if ($deptId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }

        return $query->get()->map(fn ($s) => [
            'employee'   => $s->employee->user->name,
            'code'       => $s->employee->employee_code,
            'department' => $s->employee->department?->name ?? '—',
            'position'   => $s->employee->position?->title ?? '—',
            'shift_type' => $s->shiftType?->name ?? '—',
            'start'      => $s->start_at->format('d/m/Y H:i'),
            'end'        => $s->end_at->format('d/m/Y H:i'),
            'hours'      => round($s->worked_minutes / 60, 2),
            'overtime'   => round($s->overtime_minutes / 60, 2),
            'status'     => $s->status,
        ])->toArray();
    }
}
