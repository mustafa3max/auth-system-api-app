<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('sign-in', [AuthController::class, 'signIn']);
    Route::post('sign-up', [AuthController::class, 'signUp']);
});

Route::prefix('user')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('profile', [UserController::class, 'profile']);
        Route::post('sign-out', [UserController::class, 'signOut']);
        Route::post('update', [UserController::class, 'update'])->middleware('verified');
        Route::post('delete', [UserController::class, 'delete'])->middleware('verified');

        // Email Verify
        Route::scopeBindings()->group(function () {
            Route::post('/email/verify', [UserController::class, 'verifyEmail'])->middleware('auth')->name('verification.notice');

            Route::post('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
                $request->fulfill();
                return response()->json(
                    [
                        'status' => $request->user()->email_verified_at != null,
                        'message' =>  null,
                        'data' => null,
                    ]
                );
            })->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

            Route::post('/email/verification-notification', function (Request $request) {
                $request->user()->sendEmailVerificationNotification();
                return response()->json(
                    [
                        'status' => true,
                        'message' =>  'Email verification code has been re-sent',
                        'data' => null,
                    ]
                );
            })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
        });
    });
});
