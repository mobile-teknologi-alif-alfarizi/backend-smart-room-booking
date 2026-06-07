<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KampusController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExternalApiController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api', 'jwt.activity')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

// External API Proxy Routes
Route::prefix('external')->group(function () {
    Route::get('shiftshift/instance', [ExternalApiController::class, 'getShiftShiftInstance']);
});

// Public ruangan listing for mobile booking screens.
Route::get('ruangan/public', [RuanganController::class, 'publicIndex']);
Route::get('kampus/public', [KampusController::class, 'publicIndex']);

// User Management Routes (Admin only)
Route::prefix('users')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});

// Kampus Management Routes (Admin only)
Route::prefix('kampus')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('/', [KampusController::class, 'index']);
    Route::post('/', [KampusController::class, 'store']);
    Route::get('{id}', [KampusController::class, 'show']);
    Route::put('{id}', [KampusController::class, 'update']);
    Route::delete('{id}', [KampusController::class, 'destroy']);
});

// Ruangan Management Routes (Admin only)
Route::prefix('ruangan')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('/', [RuanganController::class, 'index']);
    Route::post('/', [RuanganController::class, 'store']);
    Route::get('{id}', [RuanganController::class, 'show']);
    Route::put('{id}', [RuanganController::class, 'update']);
    Route::delete('{id}', [RuanganController::class, 'destroy']);
});

// Smart Booking Routes
Route::prefix('bookings')->middleware('auth:api', 'jwt.activity')->group(function () {
    Route::get('/', [BookingController::class, 'index']);
    Route::get('/my', [BookingController::class, 'myBookings']);
    Route::post('/', [BookingController::class, 'store']);
    Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
});

// Notification Routes
Route::prefix('notifications')->middleware('auth:api', 'jwt.activity')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('{id}/read', [NotificationController::class, 'markAsRead']);
});

// Notification Management Routes (Admin only)
Route::prefix('notifications')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('admin', [NotificationController::class, 'adminIndex']);
    Route::post('manual', [NotificationController::class, 'sendManual']);
});

// Ruangan Management Routes (Admin only)
Route::prefix('ruangan')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('/', [RuanganController::class, 'index']);
    Route::post('/', [RuanganController::class, 'store']);
    Route::get('{id}', [RuanganController::class, 'show']);
    Route::put('{id}', [RuanganController::class, 'update']);
    Route::delete('{id}', [RuanganController::class, 'destroy']);
});

// Chat/Message Routes
Route::prefix('messages')->middleware('auth:api', 'jwt.activity')->group(function () {
    Route::get('/', [MessageController::class, 'listConversations']);
    Route::get('admins', [MessageController::class, 'getAdmins']);
    Route::get('unread-count', [MessageController::class, 'unreadCount']);
    Route::get('follow-up', [MessageController::class, 'getFollowUpMessages']);
    Route::get('conversation/{userId}', [MessageController::class, 'getConversation']);
    Route::post('send', [MessageController::class, 'sendMessage']);
    Route::patch('{messageId}/seen', [MessageController::class, 'markAsSeen']);
    Route::patch('conversation/{userId}/seen-all', [MessageController::class, 'markConversationAsSeen']);
    Route::delete('{messageId}', [MessageController::class, 'deleteMessage']);
});

