<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('modules.booking.index'))->name('index');
