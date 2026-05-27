<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\PositionApiController;
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
    Route::get('employees/{employee}/shifts',        [EmployeeApiController::class, 'shifts']);
    Route::get('employees/{employee}/leave-requests', [EmployeeApiController::class, 'leaveRequests']);
});
