<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('modules.calendar.index'))->name('index');
