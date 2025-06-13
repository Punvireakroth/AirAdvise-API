<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AirQualityController;
use App\Http\Controllers\API\ForecastController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\UserLocationController;
use App\Http\Controllers\API\FeedbackController;

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


    // Air quality data - Forecast endpoints -----------
    // Forecast routes
    Route::get('/locations/{location}/forecasts', [ForecastController::class, 'getByLocation']); // WORK***

    // Air quality data - City endpoints -----------
    Route::get('/cities/search', [CityController::class, 'search']);
    Route::get('/cities/{id}', [CityController::class, 'show']);
    Route::get('/cities', [CityController::class, 'index']);

    // Air quality data - Air Quality based on Map coordinates endpoints -----------
    Route::get('/air-quality/map', [AirQualityController::class, 'getMapData']);
    Route::get('/air-quality/city/{cityId}', [AirQualityController::class, 'getCityAirQuality']);

    // Air quality data - User location/favorite cities endpoints -----------
    Route::get('/user/favorites', [UserLocationController::class, 'index']);
    Route::post('/user/favorites', [UserLocationController::class, 'store']);
    Route::delete('/user/favorites/{cityId}', [UserLocationController::class, 'destroy']);
    Route::post('/user/favorites/default', [UserLocationController::class, 'setDefault']);

    // Air quality data - Feedback endpoints -----------
    Route::post('/feedback', [FeedbackController::class, 'storeUserFeedback']);
    Route::get('/feedback', [FeedbackController::class, 'getUserFeedback']);
});
