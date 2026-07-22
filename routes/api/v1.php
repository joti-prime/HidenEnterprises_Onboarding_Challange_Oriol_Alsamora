<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V1 API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// route group for users
Route::prefix('users')->group(function () {
    Route::get('/', [V1\UserController::class, 'users'])->name('api-v1.users.all');
    Route::post('/', [V1\UserController::class, 'create'])->name('api-v1.users.create');
    Route::get('/auth', [V1\UserController::class, 'authUser'])->name('api-v1.users.get-auth')->middleware('web');
    Route::get('/username-availability', [V1\UserController::class, 'UsernameAvailable'])->name('api-v1.users.username-availability')->withoutMiddleware(['application-api', 'api']);
    Route::get('/{user}', [V1\UserController::class, 'show'])->name('api-v1.users.get');
    Route::put('/{user}', [V1\UserController::class, 'update'])->name('api-v1.users.update');
    Route::delete('/{user}', [V1\UserController::class, 'delete'])->name('api-v1.users.delete');
    Route::get('/{user}/orders', [V1\UserController::class, 'orders'])->name('api-v1.users.orders.all');
    Route::get('/{user}/payments', [V1\UserController::class, 'payments'])->name('api-v1.users.payments.all');
    Route::put('/{user}/update-balance', [V1\UserController::class, 'updateBalance'])->name('api-v1.users.update-balance');
    Route::post('/{user}/send-email', [V1\UserController::class, 'sendEmail'])->name('api-v1.users.send-email');
    Route::post('/{user}/send-notification', [V1\UserController::class, 'sendNotification'])->name('api-v1.users.send-notification');
});

// route group for products
Route::prefix('orders')->group(function () {
    Route::get('/', [V1\OrderController::class, 'orders'])->name('api-v1.orders.all');
    Route::get('/{order}', [V1\OrderController::class, 'show'])->name('api-v1.orders.get');
    Route::put('/{order}', [V1\OrderController::class, 'update'])->name('api-v1.orders.update');
    Route::delete('/{order}', [V1\OrderController::class, 'delete'])->name('api-v1.orders.delete');
    Route::post('/{order}/suspend', [V1\OrderController::class, 'suspend'])->name('api-v1.orders.suspend');
    Route::post('/{order}/unsuspend', [V1\OrderController::class, 'unsuspend'])->name('api-v1.orders.unsuspend');
    Route::post('/{order}/terminate', [V1\OrderController::class, 'terminate'])->name('api-v1.orders.terminate');
});

// route group for payments
Route::prefix('payments')->group(function () {
    Route::get('/', [V1\PaymentsController::class, 'payments'])->name('api-v1.payments.all');
    Route::post('/generate', [V1\PaymentsController::class, 'generate'])->name('api-v1.payments.generate');
});

// route group for emails
Route::prefix('emails')->group(function () {
    Route::get('/', [V1\EmailsController::class, 'emails'])->name('api-v1.emails.all');
});

// route group for categories
Route::prefix('categories')->group(function () {
    Route::get('/', [V1\CategoriesController::class, 'categories'])->name('api-v1.categories.all');
});

// route group for packages
Route::prefix('packages')->group(function () {
    Route::get('/', [V1\PackagesController::class, 'packages'])->name('api-v1.packages.all');
});

// route group for coupons
Route::prefix('coupons')->group(function () {
    Route::get('/', [V1\CouponsController::class, 'coupons'])->name('api-v1.coupons.all');
});

// route group for gateways
Route::prefix('gateways')->group(function () {
    Route::get('/', [V1\GatewaysController::class, 'gateways'])->name('api-v1.gateways.all');
});

// route group for coupons
Route::prefix('oauth-connections')->group(function () {
    Route::get('/', [V1\OauthConnectionsController::class, 'connections'])->name('api-v1.oauth-connections.all');
});

// route group for punishments (abuse-checker entrypoint)
Route::prefix('punishments')->group(function () {
    Route::post('/', [V1\PunishmentController::class, 'store'])->name('api-v1.punishments.create');
});
