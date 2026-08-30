<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ConcernController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\ConsultationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FaqTopicController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\EnsureDashboardStaffRole;
use App\Http\Middleware\EnsureOrderStaffRole;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', SiteController::class)->name('site.home');

Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('language.switch');

// Fix for default auth middleware redirection
Route::get('login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Guest Admin Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Protected Admin Routes
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Orders Management
        Route::middleware(EnsureOrderStaffRole::class)->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
            Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
            Route::post('/orders/{id}/receipt', [OrderController::class, 'uploadReceipt'])->name('orders.uploadReceipt');
            Route::put('/orders/{id}/tracking', [OrderController::class, 'updateTracking'])->name('orders.updateTracking');
        });

        Route::middleware(EnsureDashboardStaffRole::class)->group(function () {
            // Products Management
            Route::resource('products', ProductController::class);
            Route::put('products/{id}/stock', [ProductController::class, 'updateStock'])
                ->name('products.stock.update');
            Route::resource('concerns', ConcernController::class)->except(['show']);
            Route::resource('offers', OfferController::class)->except(['show']);
            Route::get('reviews', [ProductReviewController::class, 'index'])->name('reviews.index');
            Route::put('reviews/{review}/visibility', [ProductReviewController::class, 'toggleVisibility'])
                ->name('reviews.visibility');
            Route::get('settings/currency', [AppSettingsController::class, 'edit'])
                ->name('settings.currency.edit');
            Route::put('settings/currency', [AppSettingsController::class, 'update'])
                ->name('settings.currency.update');

            // Patients Management
            Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
            Route::get('patients/export', [PatientController::class, 'export'])->name('patients.export');
            Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
            Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
            Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
            Route::put('patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
            Route::delete('patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
            Route::get('patients/{id}', [PatientController::class, 'show'])->name('patients.show');
            Route::post('patients/{patient}/documents', [PatientController::class, 'storeDocument'])->name('patients.documents.store');
            Route::post('patients/progress-photos/{photo}/approve', [PatientController::class, 'approveProgressPhoto'])->name('patients.progressPhotos.approve');
            Route::post('patients/progress-photos/{photo}/reject', [PatientController::class, 'rejectProgressPhoto'])->name('patients.progressPhotos.reject');
            Route::post('patients/medical-facts/{fact}/status', [PatientController::class, 'updateMedicalFactStatus'])->name('patients.medicalFacts.status');

            // Appointments Management
            Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
            Route::get('appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
            Route::put('appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');

            // Consultations Management
            Route::get('consultations', [ConsultationController::class, 'index'])->name('consultations.index');
            Route::get('consultations/{id}', [ConsultationController::class, 'show'])->name('consultations.show');
            Route::put('consultations/{id}/status', [ConsultationController::class, 'updateStatus'])->name('consultations.updateStatus');

            // Users & Roles Management
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
            Route::put('users/{id}/role', [UserController::class, 'assignRole'])->name('users.assignRole');

            // Chat Management
            Route::post('broadcasting/auth', function (Request $request) {
                return Broadcast::auth($request);
            })->name('broadcasting.auth');
            Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
            Route::get('chats/{id}', [ChatController::class, 'show'])->name('chats.show');
            Route::post('chats/{id}/messages', [ChatController::class, 'store'])->name('chats.messages.store');
            Route::post('chats/{id}/read', [ChatController::class, 'markRead'])->name('chats.read');

            // Notifications Management
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
            Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
            Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('notifications/device-token', [NotificationController::class, 'registerDevice'])->name('notifications.deviceToken');
            Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
            Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

            // FAQ Management
            Route::resource('faq-topics', FaqTopicController::class)->only(['store', 'update', 'destroy']);
            Route::resource('faqs', FaqController::class)->except(['create', 'edit', 'show']);
        });
        
    });
});
