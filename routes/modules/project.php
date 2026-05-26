<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('modules.project.index'))->name('index');
