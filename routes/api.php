<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\PositionApiController;
use App\Http\Controllers\Api\WorkScheduleApiController;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Endpoints protégés — Module 2 : Employés
Route::middleware('auth:sanctum')->group(function () {

    // Données de référence
    Route::get('departments', [DepartmentApiController::class, 'index']);
    Route::get('positions',   [PositionApiController::class,   'index']);

    // Employés — CRUD
    Route::apiResource('employees', EmployeeApiController::class);

    // Sous-ressources d'un employé
    Route::get('employees/{employee}/shifts',          [EmployeeApiController::class,      'shifts']);
    Route::get('employees/{employee}/leave-requests',  [EmployeeApiController::class,      'leaveRequests']);
    Route::get('employees/{employee}/schedule',        [WorkScheduleApiController::class,  'employeeSchedule']);
    Route::post('employees/{employee}/schedule-override', [WorkScheduleApiController::class, 'storeOverride']);

    // Horaires périodiques
    Route::apiResource('work-schedules', WorkScheduleApiController::class);
    Route::post('work-schedules/{work_schedule}/generate-shifts', [WorkScheduleApiController::class, 'generateShifts']);
});
