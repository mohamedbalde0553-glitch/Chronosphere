<?php

use App\Modules\Shifts\Http\Controllers\DepartmentController;
use App\Modules\Shifts\Http\Controllers\WorkScheduleController;
use App\Modules\Shifts\Http\Controllers\EmployeeController;
use App\Modules\Shifts\Http\Controllers\LeaveRequestController;
use App\Modules\Shifts\Http\Controllers\RapportController;
use App\Modules\Shifts\Http\Controllers\ShiftController;
use App\Modules\Shifts\Http\Controllers\ShiftTypeController;
use App\Modules\Shifts\Http\Controllers\ShiftsController;
use App\Modules\Shifts\Http\Controllers\SkillController;
use App\Modules\Shifts\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// Accessibles à tous (hr_employee inclus)
Route::get('/', [ShiftsController::class, 'index'])->name('index');
Route::get('/planning', [ShiftsController::class, 'planning'])->name('planning');
Route::get('/planning/feed', [ShiftsController::class, 'feed'])->name('planning.feed');

// Congés : hr_employee peut soumettre/voir les siens ; approbation réservée aux managers
Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
Route::middleware('can:shifts.validate_leave')->group(function () {
    Route::put('/leaves/{leave}', [LeaveRequestController::class, 'update'])->name('leaves.update');
    Route::delete('/leaves/{leave}', [LeaveRequestController::class, 'destroy'])->name('leaves.destroy');
    Route::post('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');
});

// Gestion du département (responsable) + gestion globale (hr_manager)
Route::middleware('shift.manage.access')->group(function () {
    // Shifts CRUD
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

    // Employés
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Gestion globale — hr_manager uniquement
Route::middleware('can:shifts.manage_employees')->group(function () {
    // Départements
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Types de shifts
    Route::get('/shift-types', [ShiftTypeController::class, 'index'])->name('shift-types.index');
    Route::post('/shift-types', [ShiftTypeController::class, 'store'])->name('shift-types.store');
    Route::put('/shift-types/{shiftType}', [ShiftTypeController::class, 'update'])->name('shift-types.update');
    Route::delete('/shift-types/{shiftType}', [ShiftTypeController::class, 'destroy'])->name('shift-types.destroy');

    // Compétences
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::put('/skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');

    // Horaires périodiques
    Route::get('/schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{schedule}', [WorkScheduleController::class, 'show'])->name('schedules.show');
    Route::post('/schedules', [WorkScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [WorkScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [WorkScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{schedule}/generate', [WorkScheduleController::class, 'generateShifts'])->name('schedules.generate');
    Route::post('/schedules/{schedule}/overrides', [WorkScheduleController::class, 'storeOverride'])->name('schedules.overrides.store');
    Route::delete('/overrides/{override}', [WorkScheduleController::class, 'destroyOverride'])->name('overrides.destroy');
});

// Exports et rapports (hr_manager)
Route::middleware('can:shifts.export')->group(function () {
    Route::get('/stats', [StatsController::class, 'index'])->name('stats');
    Route::get('/export/excel', [StatsController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf-data', [StatsController::class, 'pdfData'])->name('export.pdf-data');
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::get('/rapports/pdf', [RapportController::class, 'exportPdf'])->name('rapports.pdf');
    Route::get('/rapports/excel', [RapportController::class, 'exportExcel'])->name('rapports.excel');
});
