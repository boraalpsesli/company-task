<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserDownloadController;

// Public routes
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/login/otp/verify', [UserController::class, 'verifyOtp'])->middleware('guest');
Route::post('/register', [UserController::class, 'register'])->middleware(['guest', \App\Http\Middleware\VerifyTurkishNationalId::class]);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/permissions', [UserController::class, 'assignPermissions']);
    Route::get('/check-permissions', [UserController::class, 'checkPermissions']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Company routes
    Route::apiResource('companies', CompanyController::class);
    Route::get('companies/{id}/statistics', [CompanyController::class, 'statistics']);
    Route::get('companies/statistics/all', [CompanyController::class, 'allStatistics']);

    // Team routes
    Route::apiResource('teams', TeamController::class);
    Route::get('teams/{id}/statistics', [TeamController::class, 'statistics']);
    Route::get('teams/statistics/all', [TeamController::class, 'allStatistics']);
    Route::get('teams/{teamId}/transactions', [TransactionController::class, 'teamTransactions']);

    // Transaction routes
    Route::apiResource('transactions', TransactionController::class);

    // User Download Routes
    Route::prefix('users')->group(function () {
        Route::post('/download', [UserController::class, 'download']);
        Route::get('/exports', [UserController::class, 'listExports']);
    });
});


