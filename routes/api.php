<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('sign-in', [AuthController::class, 'signIn']);
    Route::post('sign-up', [AuthController::class, 'signUp']);
});

Route::prefix('user')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('profile', [UserController::class, 'profile']);
        Route::post('sign-out', [UserController::class, 'signOut']);
        Route::post('update', [UserController::class, 'update']);
        Route::post('delete', [UserController::class, 'delete'])->middleware('verified');
    });
});
