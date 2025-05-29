<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AirQualityController;
use App\Http\Controllers\API\ForecastController;
use App\Http\Controllers\API\Admin\HealthTipController as AdminHealthTipController;
use App\Http\Controllers\API\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\CityController;


// Public routes
Route::post('/auth/register', [AuthController::class, 'register']); // WORK***
Route::post('/auth/login', [AuthController::class, 'login']); // WORK***

// Email verification
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');


// Air quality data - current data (get location)
Route::get('/air-quality/current', [AirQualityController::class, 'getCurrentByCoordinates']); // WORK***

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Air quality data - Auth endpoints -----------
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']); // WORK***

    // Send email verification
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->name('verification.send');

    // Password reset
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);

    // Air quality data - Admin endpoints -----------
    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']); // Admin: List all users
        Route::apiResource('health-tips', AdminHealthTipController::class);
        Route::apiResource('feedback', AdminFeedbackController::class);
        Route::post('/feedback/{feedback}/respond', [AdminFeedbackController::class, 'respond']);
    });

    // Air quality data - Forecast endpoints -----------
    // Forecast routes
    Route::get('/locations/{location}/forecasts', [ForecastController::class, 'getByLocation']); // WORK***

    // Air quality data - City endpoints -----------
    Route::get('/cities/search', [CityController::class, 'search']);
    Route::get('/cities/{id}', [CityController::class, 'show']);
    Route::get('/cities', [CityController::class, 'index']);
});