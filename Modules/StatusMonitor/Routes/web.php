<?php

use Illuminate\Support\Facades\Route;
use Modules\StatusMonitor\Http\Controllers\StatusController;

/*
|--------------------------------------------------------------------------
| Web Routes (StatusMonitor)
|--------------------------------------------------------------------------
|
| Loaded within the "web" middleware group. Visible to any logged in user.
|
*/

Route::get('/statusmonitor/status', [StatusController::class, 'index'])->name('statusmonitor.status');
