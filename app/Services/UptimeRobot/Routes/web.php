<?php

use Illuminate\Support\Facades\Route;
use App\Services\UptimeRobot\Http\Controllers\UptimeRobotController;

/*
|--------------------------------------------------------------------------
| Web Routes (UptimeRobot service)
|--------------------------------------------------------------------------
|
| Loaded within the "web" middleware group. All include the {order}
| parameter so WemX applies its per-service permission checks to them.
|
*/

Route::post('/uptimerobot/{order}/pause', [UptimeRobotController::class, 'pauseMonitor'])->name('uptimerobot.monitor.pause');
Route::post('/uptimerobot/{order}/resume', [UptimeRobotController::class, 'resumeMonitor'])->name('uptimerobot.monitor.resume');
Route::put('/uptimerobot/{order}/monitor', [UptimeRobotController::class, 'updateMonitor'])->name('uptimerobot.monitor.update');
