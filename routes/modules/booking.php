<?php

use App\Modules\Booking\Http\Controllers\BookingController;
use App\Modules\Booking\Http\Controllers\ReservationController;
use App\Modules\Booking\Http\Controllers\ResourceCategoryController;
use App\Modules\Booking\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [BookingController::class, 'index'])->name('index');

// Calendrier des réservations
Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');
Route::get('/calendar/feed', [BookingController::class, 'feed'])->name('calendar.feed');

// Réservations CRUD + workflow
Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
Route::put('/reservations/{booking}', [ReservationController::class, 'update'])->name('reservations.update');
Route::delete('/reservations/{booking}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
Route::post('/reservations/{booking}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
Route::post('/reservations/{booking}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
Route::post('/reservations/{booking}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

// Ressources CRUD
Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');
Route::put('/resources/{resource}', [ResourceController::class, 'update'])->name('resources.update');
Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');

// Catégories CRUD
Route::get('/categories', [ResourceCategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [ResourceCategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{category}', [ResourceCategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [ResourceCategoryController::class, 'destroy'])->name('categories.destroy');
