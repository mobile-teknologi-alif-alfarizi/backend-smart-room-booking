<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KampusController;
use App\Http\Controllers\RuanganController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExternalApiController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api', 'jwt.activity')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// External API Proxy Routes
Route::prefix('external')->group(function () {
    Route::get('shiftshift/instance', [ExternalApiController::class, 'getShiftShiftInstance']);
});

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

// Ruangan Management Routes (Admin only)
Route::prefix('ruangan')->middleware('auth:api', 'jwt.activity', 'admin')->group(function () {
    Route::get('/', [RuanganController::class, 'index']);
    Route::post('/', [RuanganController::class, 'store']);
    Route::get('{id}', [RuanganController::class, 'show']);
    Route::put('{id}', [RuanganController::class, 'update']);
    Route::delete('{id}', [RuanganController::class, 'destroy']);
});

