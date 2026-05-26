<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('modules.shifts.index'))->name('index');
