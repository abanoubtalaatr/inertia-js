<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminsController;
use App\Http\Controllers\AdvantageWebController;
use App\Http\Controllers\Banners\BannerController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\Contacts\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Notifications\NotificationController as NotificationsController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\PageWebController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SpecialistsController;
use App\Http\Controllers\UsersController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Spatie\GoogleCalendar\Event;





// Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');

/************************************************************************ */

Route::middleware(['auth'])->group(function () {

    /************************************************************************ */


    Route::resource('users', UsersController::class);
    Route::post('users/{user}/activate', [UsersController::class, 'activate'])->name('activate');
    Route::post('users/{user}', [UsersController::class, 'update'])->name('users.update'); //  inertia does not support send files using put request

    Route::post('/users/{user}/accept', [UsersController::class, 'accept'])->name('users.accept');
    Route::post('/users/{user}/reject', [UsersController::class, 'reject'])->name('users.reject');

    /************************************************************************ */

    Route::resource('admins', AdminsController::class);
    Route::post('admins/{admin}/activate', [AdminsController::class, 'activate'])->name('admins.activate');
    Route::post('admins/{admin}', [AdminsController::class, 'update'])->name('admins.update'); //  inertia does not support send files using put request
    /************************************************************************ */

    Route::resource('companies', CompaniesController::class);
    Route::post('companies/{company}/activate', [CompaniesController::class, 'activate'])->name('companies.activate');
    Route::post('companies/{company}', [CompaniesController::class, 'update'])->name('companies.update'); //  inertia does not support send files using put request
    /************************************************************************ */

    Route::resource('specialists', SpecialistsController::class);
    Route::post('specialists/{specialist}/activate', [SpecialistsController::class, 'activate'])->name('specialists.activate');
    Route::post('specialists/{specialist}', [SpecialistsController::class, 'update'])->name('specialists.update'); //  inertia does not support send files using put request
    /************************************************************************ */

    Route::resource('permissions', App\Http\Controllers\PermissionController::class);
    Route::get('permissions/{permissionId}/delete', [App\Http\Controllers\PermissionController::class, 'destroy']);

    /************************************************************************ */

    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::get('roles/{roleId}/delete', [App\Http\Controllers\RoleController::class, 'destroy']);
    Route::get('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'addPermissionToRole']);
    Route::put('roles/{roleId}/give-permissions', [App\Http\Controllers\RoleController::class, 'givePermissionToRole']);
    Route::post('roles/{role}/activate', [App\Http\Controllers\RoleController::class, 'activate'])->name('roles.activate');

    /************************************************************************ */

    Route::get('/pages/{slug}/edit', [PageWebController::class, 'edit'])->name('pages.edit');
    Route::post('/pages/{id}', [PageWebController::class, 'update'])->name('pages.update');

    /************************************************************************ */

    Route::resource('advantages', AdvantageWebController::class)->except(['update']);
    Route::post('/advantages/{advantage}', [AdvantageWebController::class, 'update'])->name('advantages.update');

    /************************************************************************ */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    /******************************* Notifications settings ***************************************** */

    Route::get('/notification-settings', [NotificationSettingsController::class, 'index'])
        ->name('notification.settings.index');
    Route::post('/notification-settings', [NotificationSettingsController::class, 'update'])
        ->name('notification.settings.update');

    /******************************* contacts***************************************** */

    Route::resource('contacts', ContactController::class);
    Route::post('/contacts/{contact}/read', [ContactController::class, 'read'])->name('contacts.read');

    /************************************************************************ */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /************************************************************************ */

    Route::get('logs', [LogController::class, 'index'])->name('logs');
    Route::get('logs/{log}', [LogController::class, 'view'])->name('logs.view');
    Route::post('logs/undo/{log}', [LogController::class, 'undo'])->name('logs.undo');
    /************************************* notifications*********************************** */

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationsController::class, 'index'])->name('index');
        Route::get('/create', [NotificationsController::class, 'create'])->name('create');
        Route::post('/', [NotificationsController::class, 'store'])->name('store');
        Route::get('/{notification}/edit', [NotificationsController::class, 'edit'])->name('edit');
        Route::post('/{notification}', [NotificationsController::class, 'update'])->name('update');
        Route::delete('/{notification}', [NotificationsController::class, 'destroy'])->name('destroy');
    });

    /*********************************banners*************************************** */

    Route::resource('banners', BannerController::class);
    Route::post('banners/{banner}/activate', [BannerController::class, 'activate'])->name('ba.activate');
    Route::post('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');

    /************************************************************************ */

    Route::resource('faqs', FaqController::class);
    Route::post('faqs/{faq}/activate', [FaqController::class, 'activate'])->name('faqs.activate');
    Route::post('/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');

    Route::resource('notification', NotificationController::class)
        ->middleware('auth')
        ->only(['index']);
});

/************************************************************************ */

Route::get('/export-users', [ExportController::class, 'export'])->name('export.users');
/************************************************************************ */

require __DIR__.'/auth.php';

Route::get('create-event', function () {

    // create a new event
    $event = new Event;

    $event->name = 'A new event';
    $event->description = 'Event description';
    $event->startDateTime = Carbon\Carbon::now();
    $event->endDateTime = Carbon\Carbon::now()->addHour();

    $event->save();

    // get all future events on a calendar
    $events = Event::get();
    dd($events);
    // update existing event
    $firstEvent = $events->first();
    $firstEvent->name = 'updated name';
    $firstEvent->save();

    $firstEvent->update(['name' => 'updated again']);

    // create a new event
    Event::create([
        'name' => 'A new event',
        'startDateTime' => Carbon\Carbon::now(),
        'endDateTime' => Carbon\Carbon::now()->addHour(),
    ]);

    // delete an event
    $event->delete();
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/about-us', [AboutUsController::class, 'edit'])->name('about-us.edit');
    Route::post('/admin/about-us', [AboutUsController::class, 'update'])->name('about-us.update');
});
