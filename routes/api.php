<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AdvantageController;
use App\Http\Controllers\Api\Auth\EmailController;
use App\Http\Controllers\Api\Auth\PhoneController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PageApiController;
use App\Http\Controllers\Api\SettingsApiController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\SpecialistController;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\Api\ToggleSuspendController;
use App\Http\Controllers\Api\UpdateOpeningHoursController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale(),
    'middleware' => ['App\Http\Middleware\Language'],
], function () {
    /*
    |--------------------------------------------------------------------------
    |  Authentication...
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
        Route::post('/reset-password', [AuthController::class, 'reset']);
    });

    /*
    |--------------------------------------------------------------------------
    |  Static pages...
    |--------------------------------------------------------------------------
    */

    Route::get('pages/{slug}', [PageApiController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    |  Contact us...
    |--------------------------------------------------------------------------
    */

    Route::post('contact-us', [ContactController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    |  Home page (landing page)...
    |--------------------------------------------------------------------------
    */

    Route::get('advantages', [AdvantageController::class, 'index']);
    Route::get('specialists', [SpecialistController::class, 'index']);
    Route::get('settings', [SettingsApiController::class, 'index'])->name('settings');
    Route::get('about-us', [AboutController::class, 'index']);
    Route::get('social-links', [SocialController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('statistics/bookings', [StatisticsController::class, 'getBookingStatistics']);
        /*
        |--------------------------------------------------------------------------
        |  Authentication...
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::put('password', [AuthController::class, 'changePassword']);

            Route::post('toggle-suspend', ToggleSuspendController::class);

            Route::post('email/update', [EmailController::class, 'update']);
            Route::post('email/verify', [EmailController::class, 'verify']);

            Route::post('phone/update', [PhoneController::class, 'update']);
            Route::post('phone/verify', [PhoneController::class, 'verify']);
            Route::post('update-opening-hours', UpdateOpeningHoursController::class);
        });

        /*
        |--------------------------------------------------------------------------
        |  Notifications...
        |--------------------------------------------------------------------------
        */
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread', [NotificationController::class, 'unread']);
        Route::post('notifications/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('notifications/mark-unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('notifications/delete', [NotificationController::class, 'delete']);
        Route::delete('notifications/delete-all', [NotificationController::class, 'deleteAll']);

        require_once __DIR__.'/client_api.php';
        require_once __DIR__.'/specialist_api.php';
    });

});
