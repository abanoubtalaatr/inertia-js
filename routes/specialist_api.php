<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\Api\Specialist\ConsultationController;
use App\Http\Controllers\Api\Specialist\PrescriptionController;
use App\Http\Controllers\Api\Specialist\ProgressController;
use App\Http\Controllers\Api\Specialist\SpecialistStatisticsController;
use App\Http\Controllers\Api\Specialist\TreatmentPlanController;
use App\Http\Controllers\Api\WorkingHoursController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('specialist')->group(function () {

    /*
    |--------------------------------------------------------------------------
    |  Bookings...
    |--------------------------------------------------------------------------
    */

    Route::get('my-bookings', [BookingController::class, 'index']);
    Route::put('bookings/{bookingId}/status', [BookingController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    |  Ratings...
    |--------------------------------------------------------------------------
    */
    Route::get('ratings', [RatingController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    |  Working-hours...
    |--------------------------------------------------------------------------
    */
    Route::post('users/{userId}/working-hours', [WorkingHoursController::class, 'store']);
    Route::get('users/{userId}/working-hours', [WorkingHoursController::class, 'index']);
    Route::get('users/{userId}/available-times', [WorkingHoursController::class, 'availableTimes']);

    /*
    |--------------------------------------------------------------------------
    |  Treatment Plans...
    |--------------------------------------------------------------------------
    */

    Route::get('treatment-plans', [TreatmentPlanController::class, 'index']);
    Route::post('treatment-plans', [TreatmentPlanController::class, 'store']);
    Route::put('treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    |  Progress...
    |--------------------------------------------------------------------------
    */
    Route::post('treatment-plans/{treatmentPlan}/progress', [ProgressController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    |  Prescriptions...
    |--------------------------------------------------------------------------
    */
    Route::get('prescriptions', [PrescriptionController::class, 'index']);
    Route::post('prescriptions', [PrescriptionController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    |  Consultation...
    |--------------------------------------------------------------------------
    */
    Route::get('consultation/{client}', [ConsultationController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    |  Specialist...
    |--------------------------------------------------------------------------
    */

    Route::get('statistics', [SpecialistStatisticsController::class, 'index']);
});
