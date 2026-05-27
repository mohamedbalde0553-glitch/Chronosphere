<?php

namespace App\Modules\Shifts\Services;

use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\EmployeeScheduleOverride;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkScheduleService
{
    /**
     * Génère les shifts pour tous les employés du département
     * sur la plage [startDate, endDate].
     * Retourne le nombre de shifts créés.
     */
    public function generateShiftsFromSchedule(int $scheduleId, string $startDate, string $endDate): int
    {
        $schedule = WorkSchedule::with(['days', 'department.employees'])->findOrFail($scheduleId);

        $employees = $this->getEmployeesForSchedule($schedule);
        $days      = $schedule->days->keyBy('day_of_week');
        $created   = 0;

        $current = Carbon::parse($startDate);
        $end     = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dow      = $current->dayOfWeek; // 0=dimanche
            $dateStr  = $current->toDateString();

            $dayConfig = $days->get($dow);

            foreach ($employees as $employee) {
                // Cherche si une override couvre ce jour
                $override = $this->findOverrideForDate($employee, $dateStr);

                // Si override pointe sur un autre schedule, on saute (sera géré par cet autre schedule)
                if ($override && $override->work_schedule_id !== $scheduleId) {
                    $current->addDay();
                    continue 2;
                }

                // Pas de config pour ce jour de la semaine → pas de shift
                if (! $dayConfig) {
                    break;
                }

                $startAt = Carbon::parse($dateStr . ' ' . $dayConfig->start_time)->toDateTimeString();
                $endAt   = Carbon::parse($dateStr . ' ' . $dayConfig->end_time)->toDateTimeString();

                // Passage minuit
                if ($dayConfig->end_time < $dayConfig->start_time) {
                    $endAt = Carbon::parse($dateStr)->addDay()->toDateString() . ' ' . $dayConfig->end_time . ':00';
                }

                // Évite les doublons
                $exists = Shift::where('employee_id', $employee->id)
                    ->where('start_at', $startAt)
                    ->exists();

                if (! $exists) {
                    Shift::create([
                        'employee_id'    => $employee->id,
                        'start_at'       => $startAt,
                        'end_at'         => $endAt,
                        'worked_minutes' => 0,
                        'status'         => 'planned',
                        'notes'          => 'Généré depuis : ' . $schedule->name,
                    ]);
                    $created++;
                }
            }

            $current->addDay();
        }

        return $created;
    }

    /**
     * Calcule les heures théoriques attendues pour un employé sur une période.
     * Retourne le total en minutes.
     */
    public function calculateExpectedHours(int $employeeId, string $startDate, string $endDate): int
    {
        $employee = Employee::findOrFail($employeeId);
        $total    = 0;

        $current = Carbon::parse($startDate);
        $end     = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $dow     = $current->dayOfWeek;

            $schedule = $this->findActiveScheduleForEmployee($employee, $dateStr);

            if ($schedule) {
                $dayConfig = $schedule->days->firstWhere('day_of_week', $dow);
                if ($dayConfig) {
                    $total += $dayConfig->workedMinutes();
                }
            }

            $current->addDay();
        }

        return $total;
    }

    /**
     * Détecte les chevauchements avec d'autres schedules actifs du même département.
     */
    public function detectConflicts(int $scheduleId): Collection
    {
        $schedule = WorkSchedule::findOrFail($scheduleId);

        $query = WorkSchedule::active()
            ->where('id', '!=', $scheduleId)
            ->where('department_id', $schedule->department_id)
            ->where('start_date', '<=', $schedule->end_date ?? '9999-12-31')
            ->where(fn($q) => $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $schedule->start_date));

        return $query->with('department')->get();
    }

    // -------------------------------------------------------------------------

    private function getEmployeesForSchedule(WorkSchedule $schedule): Collection
    {
        if (! $schedule->department_id) {
            return Employee::active()->get();
        }

        return Employee::active()
            ->where('department_id', $schedule->department_id)
            ->get();
    }

    private function findOverrideForDate(Employee $employee, string $date): ?EmployeeScheduleOverride
    {
        return $employee->scheduleOverrides()
            ->where('override_start_date', '<=', $date)
            ->where('override_end_date', '>=', $date)
            ->first();
    }

    private function findActiveScheduleForEmployee(Employee $employee, string $date): ?WorkSchedule
    {
        // Cherche d'abord un override individuel
        $override = $this->findOverrideForDate($employee, $date);
        if ($override) {
            return $override->schedule()->with('days')->first();
        }

        // Sinon, le schedule du département
        return WorkSchedule::with('days')
            ->active()
            ->forDate($date)
            ->where('department_id', $employee->department_id)
            ->first();
    }
}
