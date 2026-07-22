<?php


use App\Services\CloudFlare\Http\Controllers\AdminController;

Route::post('/save-ptero-domain/{service?}', [AdminController::class, 'saveOrderDomain'])->name('cf.pterodactyl.save.domain');
Route::prefix('/service-domain/{order}')->middleware(['auth'])->group(function () {
    Route::get('/edit', [AdminController::class, 'editOrderDomain'])->name('cf.edit.domain');
    Route::post('/update', [AdminController::class, 'updateOrderDomain'])->name('cf.update.domain');
});

