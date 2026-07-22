<?php

use App\Install\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'requirements'])->name('install.index');

    Route::get('/configuration', [InstallController::class, 'configuration'])->name('install.config');
    Route::post('/configuration', [InstallController::class, 'configuration'])->name('install.config');

    Route::get('/mail', [InstallController::class, 'mail'])->name('install.mail');
    Route::post('/mail', [InstallController::class, 'mail'])->name('install.mail');

    Route::get('/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/database', [InstallController::class, 'database'])->name('install.database');

    Route::get('/user', [InstallController::class, 'user'])->name('install.user');
    Route::post('/user', [InstallController::class, 'user'])->name('install.user');
});
