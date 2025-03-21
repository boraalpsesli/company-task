<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;

// Public routes
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');

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

    Route::apiResource('companies', CompanyController::class);

    Route::apiResource('teams', TeamController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::get('teams/{teamId}/transactions', [TransactionController::class, 'teamTransactions']);
});


