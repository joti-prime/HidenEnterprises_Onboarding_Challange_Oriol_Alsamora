<?php

use Illuminate\Support\Facades\Route;
use Modules\StatusMonitor\Http\Controllers\Admin\MonitorController;

/*
|--------------------------------------------------------------------------
| Admin Routes (StatusMonitor)
|--------------------------------------------------------------------------
|
| Loaded within the "web, admin" middleware group and prefixed with
| "admin/statusmonitor" by the RouteServiceProvider.
|
*/

Route::get('/', [MonitorController::class, 'index'])->name('admin.statusmonitor.index');
Route::get('/create', [MonitorController::class, 'create'])->name('admin.statusmonitor.create');
Route::get('/{monitor}/edit', [MonitorController::class, 'edit'])->name('admin.statusmonitor.edit');
Route::post('/', [MonitorController::class, 'store'])->name('admin.statusmonitor.store');
Route::put('/{monitor}', [MonitorController::class, 'update'])->name('admin.statusmonitor.update');
Route::delete('/{monitor}', [MonitorController::class, 'destroy'])->name('admin.statusmonitor.destroy');
Route::post('/{monitor}/toggle', [MonitorController::class, 'toggle'])->name('admin.statusmonitor.toggle');
Route::post('/{monitor}/check-now', [MonitorController::class, 'checkNow'])->name('admin.statusmonitor.check-now');
