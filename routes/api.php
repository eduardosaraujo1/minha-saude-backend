<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/login/google', [App\Http\Controllers\Api\V1\AuthController::class, 'loginWithGoogle']);
    Route::post('/auth/login/email', [App\Http\Controllers\Api\V1\AuthController::class, 'loginWithEmail']);
    Route::post('/auth/register', [App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/auth/send-email', [App\Http\Controllers\Api\V1\AuthController::class, 'sendEmail']);
    Route::post('/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Profile routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
        Route::put('/profile/name', [App\Http\Controllers\Api\V1\ProfileController::class, 'updateName']);
        Route::put('/profile/birthdate', [App\Http\Controllers\Api\V1\ProfileController::class, 'updateBirthdate']);
        Route::put('/profile/phone', [App\Http\Controllers\Api\V1\ProfileController::class, 'updatePhone']);
        Route::post('/profile/phone/verify', [App\Http\Controllers\Api\V1\ProfileController::class, 'verifyPhone']);
        Route::post('/profile/phone/send-sms', [App\Http\Controllers\Api\V1\ProfileController::class, 'sendSms']);
        Route::post('/profile/google/link', [App\Http\Controllers\Api\V1\ProfileController::class, 'linkGoogle']);
        Route::delete('/profile', [App\Http\Controllers\Api\V1\ProfileController::class, 'destroy']);
    });

    // Document routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/documents/upload', [App\Http\Controllers\Api\V1\DocumentController::class, 'upload']);
        Route::get('/documents', [App\Http\Controllers\Api\V1\DocumentController::class, 'index']);
        Route::get('/documents/{id}', [App\Http\Controllers\Api\V1\DocumentController::class, 'show']);
        Route::put('/documents/{id}', [App\Http\Controllers\Api\V1\DocumentController::class, 'update']);
        Route::delete('/documents/{id}', [App\Http\Controllers\Api\V1\DocumentController::class, 'destroy']);
        Route::post('/documents/{id}/download', [App\Http\Controllers\Api\V1\DocumentController::class, 'download']);
    });

    // Trash routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/trash', [App\Http\Controllers\Api\V1\TrashController::class, 'index']);
        Route::get('/trash/{id}', [App\Http\Controllers\Api\V1\TrashController::class, 'show']);
        Route::post('/trash/{id}/restore', [App\Http\Controllers\Api\V1\TrashController::class, 'restore']);
        Route::post('/trash/{id}/destroy', [App\Http\Controllers\Api\V1\TrashController::class, 'destroy']);
    });

    // Share routes (using /shares for Laravel convention)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/shares', [App\Http\Controllers\Api\V1\ShareController::class, 'store']);
        Route::get('/shares', [App\Http\Controllers\Api\V1\ShareController::class, 'index']);
        Route::get('/shares/{code}', [App\Http\Controllers\Api\V1\ShareController::class, 'show']);
        Route::delete('/shares/{code}', [App\Http\Controllers\Api\V1\ShareController::class, 'destroy']);
    });

    // Export routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/export/generate', function () {
            return response()->json(['status' => 'not_implemented']);
        });
    });
});