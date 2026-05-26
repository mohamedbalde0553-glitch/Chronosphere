<?php

use Illuminate\Support\Facades\Route;

// Tableau de bord du module
Route::get('/', fn () => view('modules.timetable.index'))->name('index');
