<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Using [CheckPermission::class] or 'permission' - this will check for a perm that is equal to the route name
// Using [CheckPermission::class . ':root'] or 'permission:root' - it will ignore the route name and check the perm which is written after ":", in this example we check if the user has root permissions. This method is recommended for route groups
Route::middleware(['auth', 'permission:admin.view'])->group(function () {
    Route::get('/', [Admin\Overview\OverviewController::class, 'index'])->name('admin.view');
    Route::get('/change/order/{id}/{model}/{direction}', [Admin\Overview\OverviewController::class, 'changeOrder'])->name('admin.change-order');
    Route::get('/admin-toggle-mode', [Admin\Overview\OverviewController::class, 'modeToggle'])->name('admin.toggle-mode');

    // users
    Route::get('/users/search', [Admin\UserController::class, 'search'])->name('admin.user.search');
    Route::prefix('/users/{user}')->group(function () {
        Route::post('/update-avatar', [Admin\UserController::class, 'uploadProfilePicture'])->name('admin.user.update-avatar');
        Route::post('/update-balance', [Admin\UserController::class, 'updateBalance'])->name('users.update-balance');
        Route::get('/disable-2fa', [Admin\UserController::class, 'disable2FA'])->name('admin.user.2fa.disable');
        Route::get('/email-password-reset', [Admin\UserController::class, 'emailPasswordReset'])->name('users.email-password-reset');
        Route::get('/reset-avatar', [Admin\UserController::class, 'resetProfilePicture'])->name('admin.user.reset-avatar');
        Route::get('/{device}/revoke', [Admin\UserController::class, 'revokeDevice'])->name('admin.user.devices.revoke');
        Route::get('/{device}/delete', [Admin\UserController::class, 'destroyDevice'])->name('admin.user.devices.destroy');
        Route::get('/oauth/{oauth}/unlink', [Admin\UserController::class, 'unlinkOauth'])->name('admin.user.oauth.unlink');
        Route::get('/orders', [Admin\UserController::class, 'orders'])->name('admin.user.orders');
        Route::get('/invoices', [Admin\UserController::class, 'invoices'])->name('admin.user.invoices');
        Route::get('/emails', [Admin\UserController::class, 'emails'])->name('admin.user.emails');
        Route::get('/punishments', [Admin\UserController::class, 'punishments'])->name('admin.user.punishments');
        Route::post('/punishments', [Admin\UserController::class, 'createPunishment'])->name('admin.user.punishments.create');
        Route::get('/punishments/{id}/delete', [Admin\UserController::class, 'destroyPunishment'])->name('admin.user.punishments.delete');
        Route::get('/activity', [Admin\UserController::class, 'activity'])->name('admin.user.activity');
        Route::get('/activate', [Admin\UserController::class, 'activate'])->name('admin.user.activate');
        Route::get('/verify-user', [Admin\UserController::class, 'verify'])->name('admin.user.verify');
        Route::get('/impersonate', [Admin\UserController::class, 'impersonate'])->name('admin.user.impersonate');
        Route::get('/impersonate-exit', [Admin\UserController::class, 'stopImpersonate'])->name('admin.user.impersonate.exit')->withoutMiddleware(['permission', 'permission:admin.view']);
        Route::get('/delete', [Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    // Payments
    Route::prefix('/payments')->group(function () {
        Route::get('/search', [Admin\PaymentsController::class, 'search'])->name('payments.search');
        Route::get('/create', [Admin\PaymentsController::class, 'create'])->name('payments.create');
        Route::post('/create', [Admin\PaymentsController::class, 'store'])->name('payments.store');
        Route::get('/{payment}/edit', [Admin\PaymentsController::class, 'edit'])->name('payments.edit');
        Route::post('/{payment}/edit', [Admin\PaymentsController::class, 'update'])->name('payments.update');
        Route::post('/{payment}/refund', [Admin\PaymentsController::class, 'refund'])->name('payments.refund');
        Route::post('/{payment}/complete', [Admin\PaymentsController::class, 'complete'])->name('payments.complete');
        Route::get('/{payment}/delete', [Admin\PaymentsController::class, 'destroy'])->name('payments.destroy');
        Route::get('/subscriptions/{status}', [Admin\PaymentsController::class, 'subscriptions'])->name('payments.subscriptions');
        Route::get('/{status}', [Admin\PaymentsController::class, 'index'])->name('payments.index');
    });

    // Orders
    Route::prefix('/orders')->group(function () {
        Route::get('/create', [Admin\OrdersController::class, 'create'])->name('orders.create');
        Route::post('/create', [Admin\OrdersController::class, 'store'])->name('orders.store');

        Route::get('/{order}/edit', [Admin\OrdersController::class, 'edit'])->name('orders.edit');
        Route::post('/{order}/edit', [Admin\OrdersController::class, 'update'])->name('orders.update');
        Route::get('/{order}/edit-price', [Admin\OrdersController::class, 'editPrice'])->name('orders.edit-price');
        Route::post('/{order}/edit-price', [Admin\OrdersController::class, 'updatePrice'])->name('orders.update-price');
        Route::get('/{order}/edit-service', [Admin\OrdersController::class, 'editService'])->name('orders.edit-service');
        Route::post('/{order}/price-modifiers/create', [Admin\OrdersController::class, 'createPriceModifier'])->name('orders.price-modifiers.create');
        Route::post('/{order}/price-modifiers/{modifier}/update', [Admin\OrdersController::class, 'updatePriceModifier'])->name('orders.price-modifiers.update');
        Route::delete('/{order}/price-modifiers/{modifier}/delete', [Admin\OrdersController::class, 'deletePriceModifier'])->name('orders.price-modifiers.delete');
        Route::post('/{order}/price-modifiers/apply', [Admin\OrdersController::class, 'applyPriceModifiers'])->name('orders.price-modifiers.apply');
        Route::post('/{order}/reset-daily-limit', [Admin\OrdersController::class, 'resetDailyLimit'])->name('orders.reset-daily-limit');
        Route::post('/{order}/extend', [Admin\OrdersController::class, 'extend'])->name('orders.extend');
        Route::post('/{order}/cancel', [Admin\OrdersController::class, 'cancel'])->name('orders.cancel');
        Route::get('/{order}/action/try-again', [Admin\OrdersController::class, 'tryAgain'])->name('orders.try-again');
        Route::get('/{order}/action/{action}', [Admin\OrdersController::class, 'action'])->name('orders.action');
        Route::get('/{status}', [Admin\OrdersController::class, 'index'])->name('orders.index');
        Route::get('/prices/{package}', [Admin\OrdersController::class, 'getPricesForPackage']);

        Route::get('/{order}/delete', [Admin\OrdersController::class, 'destroy'])->name('orders.destroy');
    });

    // themes
    Route::prefix('/themes')->group(function () {
        Route::get('/', [Admin\ThemeController::class, 'themes'])->name('admin.themes');
        Route::get('/activate/{theme}', [Admin\ThemeController::class, 'activate'])->name('admin.theme.activate');
        Route::get('/files/{folder}', [Admin\ThemeController::class, 'files'])->where('folder', '.*')->name('admin.theme.files');
        Route::get('/edit', [Admin\ThemeController::class, 'edit_file'])->name('admin.theme.files.edit');
        Route::any('/files/save', [Admin\ThemeController::class, 'save_file'])->name('admin.theme.file.save');
    });

    // Admin themes
    Route::prefix('/admin-themes')->group(function () {
        Route::get('/', [Admin\ThemeController::class, 'admin_themes'])->name('admin.admin_themes');
        Route::get('/activate/{theme}', [Admin\ThemeController::class, 'admin_activate'])->name('admin.admin_theme.activate');
    });

    // Updates
    Route::prefix('/updates')->group(function () {
        Route::get('/', [Admin\UpdatesController::class, 'index'])->name('updates.index');
        Route::get('/{version}/install/{type?}', [Admin\UpdatesController::class, 'install'])->name('updates.install');
        Route::get('/update-progress', [Admin\UpdatesController::class, 'trackProgress'])->name('updates.progress')->withoutMiddleware(['permission', 'permission:admin.view']);
    });

    // Punishments
    Route::prefix('/punishments')->group(function () {
        Route::get('/bans', [Admin\PunishmentController::class, 'bans'])->name('admin.bans.index');
        Route::get('/warnings', [Admin\PunishmentController::class, 'warnings'])->name('admin.warnings.index');
        Route::get('/{punishment}/unban', [Admin\PunishmentController::class, 'unban'])->name('admin.bans.unban');
        Route::get('/{punishment}/delete', [Admin\PunishmentController::class, 'destroy'])->name('admin.bans.destroy');
    });

    Route::prefix('/settings')->group(function () {
        Route::get('/general', [Admin\SettingsController::class, 'general'])->name('admin.settings');
        Route::get('/config', [Admin\SettingsController::class, 'config'])->name('admin.config');
        Route::get('/seo', [Admin\SettingsController::class, 'seo'])->name('admin.seo');
        Route::get('/taxes', [Admin\SettingsController::class, 'taxes'])->name('admin.taxes');
        Route::get('/registrations', [Admin\SettingsController::class, 'registrations'])->name('admin.registrations');
        Route::get('/oauth', [Admin\SettingsController::class, 'oauth'])->name('admin.oauth');
        Route::get('/captcha', [Admin\SettingsController::class, 'captcha'])->name('admin.captcha');
        Route::get('/maintenance', [Admin\SettingsController::class, 'maintenance'])->name('admin.maintenance');
        Route::get('/portal', [Admin\SettingsController::class, 'portal'])->name('admin.settings.portal');
        Route::get('/theme', [Admin\SettingsController::class, 'theme'])->name('admin.settings.theme');
        Route::any('/store', [Admin\SettingsController::class, 'store'])->name('admin.settings.store');
    });

    // Emails
    Route::prefix('/emails')->group(function () {
        Route::get('/', [Admin\EmailsController::class, 'history'])->name('emails.history');
        Route::post('/send', [Admin\EmailsController::class, 'sendEmail'])->name('emails.send');
        Route::get('/configure', [Admin\EmailsController::class, 'configure'])->name('emails.configure');
        Route::get('/send-test', [Admin\EmailsController::class, 'testMail'])->name('emails.test');
        Route::get('/messages', [Admin\EmailsController::class, 'messages'])->name('emails.messages');
        Route::post('/messages', [Admin\EmailsController::class, 'updateMessages'])->name('emails.messages.update');
        Route::get('/templates', [Admin\EmailsController::class, 'templates'])->name('emails.templates');
        Route::get('/{email}/resend', [Admin\EmailsController::class, 'resend'])->name('emails.resend');
        Route::get('/{email}/destroy', [Admin\EmailsController::class, 'destroy'])->name('emails.destroy');
        Route::get('/preview', [Admin\EmailsController::class, 'preview'])->name('emails.preview');

        Route::get('/mass-mailer', [Admin\EmailsController::class, 'massMailer'])->name('emails.mass-mailer');
        Route::get('/mass-mailer-create', [Admin\EmailsController::class, 'createMassMail'])->name('emails.mass-mailer.create');
        Route::post('/mass-mailer-store', [Admin\EmailsController::class, 'storeMassMail'])->name('emails.mass-mailer.store');
        Route::get('/mass-mailer/{mass_mail}/edit', [Admin\EmailsController::class, 'editMassMail'])->name('emails.mass-mailer.edit');
        Route::post('/mass-mailer/{mass_mail}/update', [Admin\EmailsController::class, 'updateMassMail'])->name('emails.mass-mailer.update');
        Route::get('/mass-mailer/{mass_mail}/destroy', [Admin\EmailsController::class, 'destroyMassMail'])->name('emails.mass-mailer.destroy');

    })->middleware('permission');

    Route::prefix('/webhooks')->group(function () {
        Route::get('/', [Admin\WebhooksController::class, 'index'])->name('webhooks.index');
    });

    Route::prefix('/logs')->group(function () {
        Route::get('/', [Admin\LogsController::class, 'index'])->name('logs.index');
    });

    Route::prefix('/marketplace')->group(function () {
        Route::get('/', [Admin\MarketplaceController::class, 'index'])->name('admin.marketplace');
        Route::get('/view/{resource}', [Admin\MarketplaceController::class, 'view'])->name('admin.marketplace.view');
        Route::get('install/{resource_id}/{version_id}', [Admin\Overview\OverviewController::class, 'resourceInstall'])->name('admin.resource.install');
    });

    Route::prefix('/services')->group(function () {
        Route::get('/', [Admin\ServicesController::class, 'index'])->name('services.view');
        Route::get('/{service}/config', [Admin\ServicesController::class, 'config'])->name('services.config');
        Route::post('/{service}/store', [Admin\ServicesController::class, 'store'])->name('services.store');
        Route::get('/{service}/test-connection', [Admin\ServicesController::class, 'testConnection'])->name('services.test-connection');
    });

    Route::prefix('/widgets')->group(function () {
        Route::get('/', [Admin\WidgetsController::class, 'index'])->name('widgets.index');
    });

    Route::prefix('/modules')->group(function () {
        Route::get('/', [Admin\ModulesController::class, 'index'])->name('modules.view');
        Route::get('/{module}/toggle', [Admin\ModulesController::class, 'toggleStatus'])->name('modules.toggle');
        Route::get('/{module}/delete', [Admin\ModulesController::class, 'delete'])->name('modules.delete');
    });

    Route::prefix('/packages/prices')->group(function () {
        Route::post('/{package}/create', [Admin\PackagesController::class, 'createPrice'])->name('package_price.create');
        Route::post('/{price}/update', [Admin\PackagesController::class, 'updatePrice'])->name('package_price.update');
        Route::get('/{price}/delete', [Admin\PackagesController::class, 'deletePrice'])->name('package_price.delete');
    });

    Route::prefix('/packages/emails')->group(function () {
        Route::post('/{package}/create', [Admin\PackagesController::class, 'createEmail'])->name('packages.emails.create');
        Route::post('/{email}/update', [Admin\PackagesController::class, 'updateEmail'])->name('packages.emails.update');
        Route::get('/{email}/delete', [Admin\PackagesController::class, 'deleteEmail'])->name('packages.emails.delete');
    });

    Route::prefix('/packages/webhooks')->group(function () {
        Route::post('/{package}/create', [Admin\PackagesController::class, 'createWebhook'])->name('packages.webhooks.create');
        Route::post('/{webhook}/update', [Admin\PackagesController::class, 'updateWebhook'])->name('packages.webhooks.update');
        Route::get('/{webhook}/delete', [Admin\PackagesController::class, 'deleteWebhook'])->name('packages.webhooks.delete');
    });

    Route::prefix('/packages/{package}')->group(function () {
        Route::get('/features', [Admin\PackagesController::class, 'editFeatures'])->name('packages.features');
        Route::get('/prices', [Admin\PackagesController::class, 'editPrices'])->name('packages.prices');
        Route::get('/service', [Admin\PackagesController::class, 'editService'])->name('packages.service');
        Route::get('/emails', [Admin\PackagesController::class, 'editEmails'])->name('packages.emails');
        Route::get('/webhooks', [Admin\PackagesController::class, 'editWebhooks'])->name('packages.webhooks');
        Route::get('/links', [Admin\PackagesController::class, 'editLinks'])->name('packages.links');

        Route::post('/feature/create', [Admin\PackagesController::class, 'createFeature'])->name('package.create-feature');
        Route::get('/{feature}/move/{direction}', [Admin\PackagesController::class, 'moveFeature'])->name('package.move-feature');
        Route::get('/{feature}/delete', [Admin\PackagesController::class, 'destroyFeature'])->name('package.destroy-feature');
        Route::post('{feature}/update-feature', [Admin\PackagesController::class, 'updateFeature'])->name('package.feature-update');
        Route::post('/update-service-data', [Admin\PackagesController::class, 'updateServiceData'])->name('package.update-service');

        Route::get('/config-options', [Admin\PackagesController::class, 'configOptions'])->name('packages.config-options');
        Route::post('/config-options', [Admin\PackagesController::class, 'addConfigOption'])->name('packages.config-options.add');
        Route::get('/config-options/{option}/edit', [Admin\PackagesController::class, 'editConfigOption'])->name('packages.config-options.edit-option');
        Route::post('/config-options/{option}/update', [Admin\PackagesController::class, 'updateConfigOption'])->name('packages.config-options.update-option');
        Route::get('/config-options/{option}/move', [Admin\PackagesController::class, 'moveConfigOption'])->name('packages.config-options.move-option');
        Route::get('/config-options/{option}/delete', [Admin\PackagesController::class, 'deleteConfigOption'])->name('packages.config-options.delete-option');

    });

    Route::get('/packages/update-service/{package}/{service}', [Admin\PackagesController::class, 'updateService'])->name('packages.update-service');
    Route::get('/packages/clone/{package}', [Admin\PackagesController::class, 'clonePackage'])->name('packages.clone');

    Route::get('/permissions/import', [Admin\PermissionController::class, 'import'])->name('permissions.import');
    Route::get('/gateways/toggle/{gateway}', [Admin\GatewayController::class, 'toggle'])->name('gateways.toggle');
    Route::get('/gateways/default/{gateway}', [Admin\GatewayController::class, 'default'])->name('gateways.default');
    Route::resource('api-v1', Admin\ApiKeyController::class)->except(['edit', 'update']);
    Route::resource('gateways', Admin\GatewayController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('packages', Admin\PackagesController::class);
    Route::resource('coupons', Admin\CouponController::class);
    Route::resource('users', Admin\UserController::class);
    Route::get('/groups/users/{group}', [Admin\GroupController::class, 'showUsers'])->name('groups.users');
    Route::resource('groups', Admin\GroupController::class);
    Route::resource('permissions', Admin\PermissionController::class);

    Route::get('/pages/translation/{id}', [Admin\PagesController::class, 'translation'])->name('pages.translation');
    Route::get('/pages/translation/edit/{id}/{locale?}', [Admin\PagesController::class, 'translationEdit'])->name('pages.translation.edit');
    Route::put('/pages/translation/store/{id}', [Admin\PagesController::class, 'translationStore'])->name('pages.translation.store');
    Route::delete('/pages/translation/{translation}', [Admin\PagesController::class, 'translationDestroy'])->name('pages.translation.destroy');
    Route::resource('pages', Admin\PagesController::class);

    Route::get('/articles/translation/{id}', [Admin\ArticlesController::class, 'translation'])->name('articles.translation');
    Route::get('/articles/translation/edit/{id}/{locale?}', [Admin\ArticlesController::class, 'translationEdit'])->name('articles.translation.edit');
    Route::put('/articles/translation/store/{id}', [Admin\ArticlesController::class, 'translationStore'])->name('articles.translation.store');
    Route::delete('/articles/translation/{translation}', [Admin\ArticlesController::class, 'translationDestroy'])->name('articles.translation.destroy');
    Route::resource('articles', Admin\ArticlesController::class);
});
