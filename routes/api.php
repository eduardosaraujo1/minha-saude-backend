<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ShareController;
use App\Http\Controllers\Api\V1\TrashController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/login/google', [AuthController::class, 'loginGoogle'])
        ->name('auth.login.google');
    Route::post('/auth/login/email', [AuthController::class, 'loginEmail'])
        ->name('auth.login.email');
    Route::post('/auth/register', [AuthController::class, 'register'])
        ->name('auth.register');
    Route::post('/auth/send-email', [AuthController::class, 'sendEmail'])
        ->name('auth.send.email');
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');

    // Profile routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [ProfileController::class, 'getProfile']);
        Route::put('/profile/name', [ProfileController::class, 'putName']);
        Route::put('/profile/birthdate', [ProfileController::class, 'putBirthdate']);
        Route::put('/profile/phone', [ProfileController::class, 'putPhone']);
        Route::post('/profile/phone/verify', [ProfileController::class, 'phoneVerify']);
        Route::post('/profile/phone/send-sms', [ProfileController::class, 'phoneSendSms']);
        Route::post('/profile/google/link', [ProfileController::class, 'googleLink']);
        Route::delete('/profile', [ProfileController::class, 'deleteProfile']);
    });

    // Document routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/documents/upload', [DocumentController::class, 'upload']);
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::get('/documents/{id}', [DocumentController::class, 'show']);
        Route::put('/documents/{id}', [DocumentController::class, 'update']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
        Route::post('/documents/{id}/download', [DocumentController::class, 'download']);
    });

    // Trash routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/trash', [TrashController::class, 'index']);
        Route::get('/trash/{id}', [TrashController::class, 'show']);
        Route::post('/trash/{id}/restore', [TrashController::class, 'restore']);
        Route::post('/trash/{id}/destroy', [TrashController::class, 'destroy']);
    });

    // Share routes (using /shares for Laravel convention)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/shares', [ShareController::class, 'store']);
        Route::get('/shares', [ShareController::class, 'index']);
        Route::get('/shares/{code}', [ShareController::class, 'show']);
        Route::delete('/shares/{code}', [ShareController::class, 'destroy']);
    });

    // Export routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/export/generate', function () {
            return response()->json(['status' => 'not_implemented']);
        });
    });
});
