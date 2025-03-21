<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


