<?php

use App\Modules\Project\Http\Controllers\ProjectController;
use App\Modules\Project\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [ProjectController::class, 'index'])->name('index');

// Projets CRUD
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

// Vues projet
Route::get('/projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');
Route::get('/projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');

// Tâches CRUD (AJAX)
Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

// Commentaires
Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
Route::delete('/comments/{comment}', [TaskController::class, 'destroyComment'])->name('comments.destroy');
