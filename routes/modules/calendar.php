<?php

use App\Modules\Calendar\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index'])->name('index');
Route::get('/feed', [CalendarController::class, 'feed'])->name('feed');

Route::post('/events', [CalendarController::class, 'store'])->name('events.store');
Route::get('/events/{event}', [CalendarController::class, 'show'])->name('events.show');
Route::put('/events/{event}', [CalendarController::class, 'update'])->name('events.update');
Route::delete('/events/{event}', [CalendarController::class, 'destroy'])->name('events.destroy');

Route::post('/calendars', [CalendarController::class, 'storeCalendar'])->name('calendars.store');
Route::delete('/calendars/{calendar}', [CalendarController::class, 'destroyCalendar'])->name('calendars.destroy');
