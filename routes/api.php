<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserPreferenceController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\AirQualityController;
use App\Http\Controllers\API\ForecastController;
use App\Http\Controllers\API\HealthTipController;
use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\Admin\HealthTipController as AdminHealthTipController;
use App\Http\Controllers\API\Admin\FeedbackController as AdminFeedbackController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Air quality data - some endpoints public
Route::get('/air-quality', [AirQualityController::class, 'getCurrentByCoordinates']);
Route::get('/activities', [ActivityController::class, 'index']);
Route::get('/health-tips/public', [HealthTipController::class, 'public']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // User profile
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);

    // User preferences
    Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
    Route::put('/user/preferences', [UserPreferenceController::class, 'update']);

    // Locations
    Route::apiResource('locations', LocationController::class);
    Route::post('/locations/{location}/favorite', [LocationController::class, 'toggleFavorite']);

    // Air quality data for user's locations
    Route::get('/locations/{location}/air-quality', [AirQualityController::class, 'getByLocation']);
    Route::get('/locations/{location}/forecast', [ForecastController::class, 'getByLocation']);

    // Health tips
    Route::get('/health-tips', [HealthTipController::class, 'index']);

    // Notifications
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'update']);

    // Feedback
    Route::apiResource('feedback', FeedbackController::class);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('health-tips', AdminHealthTipController::class);
        Route::apiResource('feedback', AdminFeedbackController::class);
        Route::post('/feedback/{feedback}/respond', [AdminFeedbackController::class, 'respond']);
    });
});
