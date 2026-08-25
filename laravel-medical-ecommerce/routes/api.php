<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DeliveryAreaController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PatientProgressPhotoController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\EnsureDashboardStaffRole;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp']);
    Route::post('/resend-registration-otp', [AuthController::class, 'resendRegistrationOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

// Public browsing/chatbot routes for guest mode
Route::get('/home', HomeController::class);
Route::get('/app-settings', AppSettingsController::class);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/catalog-filters', [ProductController::class, 'catalogFilters']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/bot/bootstrap', [BotController::class, 'bootstrap']);
Route::post('/bot/ask', [BotController::class, 'ask']);
Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
Route::get('/delivery-areas', [DeliveryAreaController::class, 'index']);
Route::get('/offers/active', [OfferController::class, 'active']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Product management (admin/doctor side)
    Route::middleware(EnsureDashboardStaffRole::class)->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::apiResource('faqs', FaqController::class)->only(['store', 'update', 'destroy']);
    });

    // Bot conversation history for authenticated patients.
    Route::get('/bot/conversation', [BotController::class, 'conversation']);

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{itemId}', [CartController::class, 'update']);
        Route::delete('/{itemId}', [CartController::class, 'destroy']);
    });

    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/{id}/confirm', [OrderController::class, 'confirm']);
        Route::post('/{id}/delivered', [OrderController::class, 'markDelivered']);
    });

    // Patients and Appointments
    Route::apiResource('patients', PatientController::class);
    Route::post('/patient-progress-photos', [PatientProgressPhotoController::class, 'store']);
    Route::apiResource('appointments', AppointmentController::class);

    // Chat System
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'index']);
        Route::post('/conversations', [ChatController::class, 'startConversation']);
        Route::get('/conversations/{id}/messages', [ChatController::class, 'messages']);
        Route::post('/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
    });

    // Consultations
    Route::prefix('consultations')->group(function () {
        Route::get('/', [ConsultationController::class, 'index']);
        Route::post('/', [ConsultationController::class, 'store']);
        Route::get('/{id}', [ConsultationController::class, 'show']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
    });

    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
});
