<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\Client\MedicalHistoryController;
use App\Http\Controllers\Api\Client\PrescriptionController;
use App\Http\Controllers\Api\Client\TreatmentPlanController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\Api\WorkingHoursController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('client')->group(function () {

    /*
    |--------------------------------------------------------------------------
    |  Rating...
    |--------------------------------------------------------------------------
    */

    Route::post('ratings', [RatingController::class, 'store']);
    Route::get('ratings', [RatingController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    |  my-bookings...
    |--------------------------------------------------------------------------
    */

    Route::get('my-bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::put('bookings/{bookingId}/status', [BookingController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    |  Treatment-plans...
    |--------------------------------------------------------------------------
    */

    Route::get('treatment-plans', [TreatmentPlanController::class, 'index']);
    Route::get('treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    |  Prescriptions...
    |--------------------------------------------------------------------------
    */

    Route::get('prescriptions', [PrescriptionController::class, 'index']);
    Route::get('prescriptions/{prescription}', [PrescriptionController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    |  Medical-history...
    |--------------------------------------------------------------------------
    */
    Route::get('medical-history', [MedicalHistoryController::class, 'show']);
    Route::put('medical-history', [MedicalHistoryController::class, 'update']);
});
Route::prefix('client')->group(function () {
    /*
    |--------------------------------------------------------------------------
    |  available-times...
    |--------------------------------------------------------------------------
    */
    Route::get('users/{userId}/available-times', [WorkingHoursController::class, 'availableTimes']);

});
