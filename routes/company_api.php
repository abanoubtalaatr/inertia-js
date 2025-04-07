<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\API\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:company')->prefix('company')->group(function () {
    // Specialists
    Route::get('specialists', [AdminController::class, 'companySpecialists']);
    Route::post('specialists', [AdminController::class, 'registerSpecialist']);
    Route::put('specialists/{id}/status', [AdminController::class, 'updateSpecialistStatus']);

    // Appointments
    Route::get('appointments', [AppointmentController::class, 'companyAppointments']);

    // Consultations
    Route::get('consultations', [ConsultationController::class, 'companyConsultations']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Statistics
    Route::get('statistics', [StatisticsController::class, 'companyStatistics']);
});
