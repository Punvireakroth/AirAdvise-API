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
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\LocationSearchController;


// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Email verification
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');



// Air quality data - some endpoints public
Route::get('/air-quality/current', [AirQualityController::class, 'getCurrentByCoordinates']);
Route::get('/activities', [ActivityController::class, 'index']);
Route::get('/health-tips/public', [HealthTipController::class, 'public']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Send email verification
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->name('verification.send');

    // User profile
    Route::get('/user', [AuthController::class, 'profile']);

    // Profile management
    Route::get('/profile', [UserController::class, 'show']);
    Route::put('/profile', [UserController::class, 'update']);

    // Password reset
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);

    // User preferences
    Route::get('/preferences', [UserPreferenceController::class, 'show']);
    Route::put('/preferences', [UserPreferenceController::class, 'update']);

    // Locations
    Route::apiResource('locations', LocationController::class);
    Route::post('/locations/{location}/favorite', [LocationController::class, 'toggleFavorite']);

    // Location search
    Route::get('/location/search', [LocationSearchController::class, 'search']);
    Route::get('/location/reverse-geocode', [LocationSearchController::class, 'reverseGeocode']);

    // Location historical air quality data
    Route::get('/locations/{location}/air-quality/historical', [AirQualityController::class, 'getHistorical']);

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
        Route::get('/users', [UserController::class, 'index']); // Admin: List all users
        Route::apiResource('health-tips', AdminHealthTipController::class);
        Route::apiResource('feedback', AdminFeedbackController::class);
        Route::post('/feedback/{feedback}/respond', [AdminFeedbackController::class, 'respond']);
    });
});