<?php

use App\Modules\Shifts\Http\Controllers\DepartmentController;
use App\Modules\Shifts\Http\Controllers\EmployeeController;
use App\Modules\Shifts\Http\Controllers\LeaveRequestController;
use App\Modules\Shifts\Http\Controllers\ShiftController;
use App\Modules\Shifts\Http\Controllers\ShiftTypeController;
use App\Modules\Shifts\Http\Controllers\ShiftsController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [ShiftsController::class, 'index'])->name('index');

// Planning (grille FullCalendar)
Route::get('/planning', [ShiftsController::class, 'planning'])->name('planning');
Route::get('/planning/feed', [ShiftsController::class, 'feed'])->name('planning.feed');

// Shifts CRUD (AJAX)
Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

// Employés
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

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

// Congés
Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
Route::put('/leaves/{leave}', [LeaveRequestController::class, 'update'])->name('leaves.update');
Route::delete('/leaves/{leave}', [LeaveRequestController::class, 'destroy'])->name('leaves.destroy');
Route::post('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
Route::post('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');
