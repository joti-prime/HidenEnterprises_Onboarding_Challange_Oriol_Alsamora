<?php


use App\Services\CloudFlare\Http\Controllers\AdminController;


Route::get('/pterodactyl', [AdminController::class, 'pterodactyl'])->name('admin.cf.pterodactyl');
Route::post('/pterodactyl', [AdminController::class, 'pteroStore'])->name('admin.cf.pterodactyl.store');
Route::post('/pterodactyl/{cfService}', [AdminController::class, 'pteroUpdate'])->name('admin.cf.pterodactyl.update');
Route::get('/pterodactyl/{cfService}/delete', [AdminController::class, 'pteroDestroy'])->name('admin.cf.pterodactyl.destroy');
