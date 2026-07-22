<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Middleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [Client\PortalController::class, 'index'])->name('portal');
Route::get('/p/{page}', [Client\DashboardController::class, 'page'])->name('page');
Route::get('/page/{page}', [Client\DashboardController::class, 'page']);

// Store group => /store
Route::prefix('store')->group(function () {
    Route::get('/', [Client\StoreController::class, 'index'])->name('store.index');
    Route::get('/{service}', [Client\StoreController::class, 'service'])->name('store.service');
    Route::get('/view/{package}', [Client\StoreController::class, 'viewPackage'])->name('store.package');
    Route::get('/{package}/pricing', [Client\StoreController::class, 'pricing'])->name('store.pricing');
    Route::get('/validate-coupon/{package}/{code}', [Client\StoreController::class, 'validateCoupon'])->name('validate-coupon');
});

// Suspended page also opts out of 2FA / forced-2FA / revoked-device middlewares so
// a suspended user whose 2FA session expired (or whose device got revoked) does
// not bounce between /suspended and /2fa.validate / /reauthenticate forever.
Route::get('/suspended', [Client\DashboardController::class, 'suspended'])->name('suspended')->withoutMiddleware([
    Middleware\Punishments::class,
    Middleware\TwoFactorAuthentication::class,
    Middleware\ForceTwoFa::class,
    Middleware\CheckRevokedDevices::class,
]);

Route::middleware('auth')->group(function () {


    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/news')->group(function () {
        Route::get('/', [Client\NewsController::class, 'index'])->name('news.index')->withoutMiddleware('auth');
        Route::get('/{article}', [Client\NewsController::class, 'article'])->name('news.article')->withoutMiddleware('auth');
        Route::post('/{article}/comment', [Client\NewsController::class, 'comment'])->name('news.comment')->middleware('throttle:5,15');
        Route::get('/{article}/{emoji}', [Client\NewsController::class, 'react'])->name('news.react');
        Route::get('/rating/{article}/{rating}', [Client\NewsController::class, 'helpful'])->name('news.helpful')->withoutMiddleware('auth');
        Route::get('/comment/{comment}/upvote', [Client\NewsController::class, 'upvoteComment'])->name('news.comments.upvote')->middleware('throttle:3,15');
        Route::get('/comment/{comment}/downvote', [Client\NewsController::class, 'downvoteComment'])->name('news.comments.downvote')->middleware('throttle:3,15');
        Route::get('/comment/{comment}/remove', [Client\NewsController::class, 'removeComment'])->name('news.comments.remove');
        Route::get('/comment/{comment}/report', [Client\NewsController::class, 'reportComment'])->name('news.comments.report');

    });

    // Filter Routes
    Route::prefix('filter')->group(function () {
        Route::any('/order/{status}', [Client\DashboardController::class, 'filterOrders'])->name('filter-orders');
    });

    Route::prefix('invites')->group(function () {
        Route::get('/', [Client\DashboardController::class, 'invites'])->name('invites.index');
        Route::get('/{invite}/accept', [Client\DashboardController::class, 'acceptInvite'])->name('invites.accept');
        Route::get('/{invite}/reject', [Client\DashboardController::class, 'rejectInvite'])->name('invites.reject');
    });

    // Service Routes
    Route::prefix('service')->group(function () {
        Route::any('/{order}/{page}', [Client\ServiceController::class, 'service'])->name('service');
        Route::get('/package/{package}/prices', [Client\ServiceController::class, 'getPackagePrices'])->name('package.prices');
    });

    // Configurable Options Upgrade Routes
    Route::prefix('configurable-options')->group(function () {
        Route::get('/{order}/upgrade', [Client\ConfigurableOptionUpgradeController::class, 'show'])->name('configurable-options.show');
        Route::post('/{order}/upgrade', [Client\ConfigurableOptionUpgradeController::class, 'upgrade'])->name('configurable-options.upgrade');
    });

    // Notifications group => /notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/mark-all-as-read', [Client\NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-as-read');
    });

    // Email History => /email-history
    Route::prefix('email-history')->group(function () {
        Route::get('/', [Client\EmailHistoryController::class, 'index'])->name('email.history');
        Route::get('/{email}/download', [Client\EmailHistoryController::class, 'download'])->name('email.download');
    });

    // Email History => /email-history
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [Client\SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/store', [Client\SubscriptionController::class, 'store'])->name('subscription.store');
        Route::get('/prices/{package}', [Client\SubscriptionController::class, 'getPricesForPackage'])->name('subscription.prices');
    });

    // User Settings group => /user/settings
    Route::prefix('/user/settings')->group(function () {
        Route::get('/', [Client\UserController::class, 'settings'])->name('user.settings');
        Route::post('/update', [Client\UserController::class, 'updateUser'])->name('update-username');
        Route::post('/update-address', [Client\UserController::class, 'updateAddress'])->name('update-address')->withoutMiddleware([Middleware\Maintenance::class, Middleware\RequireAddress::class]);
        Route::post('/upload-avatar', [Client\UserController::class, 'uploadProfilePicture'])->name('upload-profile-picture');
        Route::delete('/delete-avatar', [Client\UserController::class, 'deleteProfilePicture'])->name('delete-profile-picture');
        Route::post('/update-email', [Client\UserController::class, 'updateEmail'])->name('update-email');
        Route::post('/update-password', [Client\UserController::class, 'updatePassword'])->name('update-password');
        Route::post('/download-data', [Client\UserController::class, 'downloadUserData'])->name('user.download-data');
        Route::get('/revoke/{device}', [Client\UserController::class, 'revoke'])->name('revoke');
        Route::get('/visibility/{status}', [Client\UserController::class, 'visibility'])->name('user.visibility');
        Route::post('/request-removal', [Client\UserController::class, 'deleteUserAccount'])->name('user.request-removal');
        Route::get('/request-cancel', [Client\UserController::class, 'cancelDeleteUserAccount'])->name('user.cancel-removal');
    });

    Route::prefix('/balance')->group(function () {
        Route::get('/', [Client\DashboardController::class, 'balance'])->name('balance');
        Route::any('/add', [Client\PaymentController::class, 'createBalancePayment'])->name('balance.add');
    });

    Route::prefix('/oauth/{service}')->group(function () {
        Route::get('/login', [Auth\OauthController::class, 'login'])->name('oauth.login')->withoutMiddleware('auth');
        Route::get('/connect', [Auth\OauthController::class, 'connect'])->name('oauth.connect');
        Route::get('/remove', [Auth\OauthController::class, 'remove'])->name('oauth.remove');
        Route::get('/redirect', [Auth\OauthController::class, 'callback'])->name('oauth.callback')->withoutMiddleware('auth');
    });

    Route::prefix('/invoices')->group(function () {
        Route::get('/', [Client\DashboardController::class, 'invoices'])->name('invoices');
    });

    Route::prefix('payment')->group(function () {
        Route::any('/package/{package}', [Client\PaymentController::class, 'createOrderPayment'])->name('payment.package');
        Route::any('/process/{gateway}/{payment}', [Client\PaymentController::class, 'processPayment'])->name('payment.process');
        Route::post('/validate', [Client\PaymentController::class, 'validatePayment'])->name('payment.validate');
        Route::any('/return/{gateway}', [Client\PaymentController::class, 'paymentReturn'])->name('payment.return')->withoutMiddleware('auth');
        Route::any('/create/order/subscription', [Client\PaymentController::class, 'createOrderSubscription'])->name('payment.create_order_subscription');

        Route::any('/cancel/{payment}', [Client\PaymentController::class, 'paymentCancel'])->name('payment.cancel');
        Route::any('/success/{payment}', [Client\PaymentController::class, 'paymentSuccess'])->name('payment.success');

        Route::get('/invoice/{payment}', [Client\PaymentController::class, 'invoice'])->name('invoice');
        Route::get('/invoice/{payment}/download', [Client\PaymentController::class, 'downloadInvoice'])->name('invoice.download');
        Route::any('/invoice/{payment}/pay', [Client\PaymentController::class, 'payInvoice'])->name('invoice.pay');
        Route::post('/validate', [Client\PaymentController::class, 'validatePayment'])->name('payment.validate');
    });

    Route::prefix('/restricted')->withoutMiddleware([Middleware\Maintenance::class])->group(function () {
        Route::get('/account-activation', [Client\MaintenanceController::class, 'activation'])->name('restricted.activation');
        Route::get('/maintenance', [Client\MaintenanceController::class, 'maintenance'])->name('restricted.maintenance');
    });

    Route::prefix('support/chat')->group(function () {
        Route::get('/', [Client\SupportChatController::class, 'chat'])->name('support.chat');
        Route::get('/req', [Client\SupportChatController::class, 'interactWithChatGPT'])->name('support.req');
    });

});

// Custom assets
Route::get('assets/{template}/{path}', function ($template, $path) {
    $path = resource_path('themes/client/' . $template . '/assets/' . $path);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->name('client-assets');

// Temporal fix for FreePterodactyl routes
if (file_exists(base_path('app/Services/FreePterodactyl/Routes/web.php'))) {
    require base_path('app/Services/FreePterodactyl/Routes/web.php');
}