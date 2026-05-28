<?php

use App\Modules\Timetable\Http\Controllers\ClassGroupController;
use App\Modules\Timetable\Http\Controllers\RoomController;
use App\Modules\Timetable\Http\Controllers\SessionController;
use App\Modules\Timetable\Http\Controllers\SubjectController;
use App\Modules\Timetable\Http\Controllers\TeacherController;
use App\Modules\Timetable\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

// Accessibles à tous les rôles timetable (student, teacher, admin)
Route::get('/', [TimetableController::class, 'index'])->name('index');
Route::get('/schedule', [TimetableController::class, 'schedule'])->name('schedule');
Route::get('/schedule/feed', [TimetableController::class, 'feed'])->name('schedule.feed');

// Admin timetable uniquement (timetable.create / timetable.manage_rooms)
Route::middleware('can:timetable.create')->group(function () {
    // Sessions CRUD
    Route::post('/sessions', [SessionController::class, 'store'])->name('sessions.store');
    Route::put('/sessions/{session}', [SessionController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::get('/sessions/{session}/conflicts', [SessionController::class, 'conflicts'])->name('sessions.conflicts');
    Route::post('/courses/{course}/generate-sessions', [SessionController::class, 'generateFromSchedule'])->name('courses.generate-sessions');
});

Route::middleware('can:timetable.manage_rooms')->group(function () {
    // Salles
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    // Matières
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Groupes
    Route::get('/groups', [ClassGroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [ClassGroupController::class, 'store'])->name('groups.store');
    Route::put('/groups/{group}', [ClassGroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [ClassGroupController::class, 'destroy'])->name('groups.destroy');
});

Route::middleware('can:timetable.manage_teachers')->group(function () {
    // Enseignants
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
});
