<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Public routes
Route::middleware('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/countries', [CountriesController::class, 'getAll'])->name('countries.getAll');
    Route::post('/signup', [RegisterController::class, 'register'])->name('signup');
    Route::post('/otp/regenerate', [RegisterController::class, 'regenerateOtp'])->name('otp.regenerate')
                                                                                 ->middleware('throttle:1,2');
    Route::post('/confirm_otp', [RegisterController::class, 'validateOtp'])->name('confirmOtp');
    Route::post('/change/password', [RegisterController::class, 'changePassword'])->name('changePassword');
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Add more routes as needed
});

// Email verification routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/email/verification-notification', [VerificationController::class, 'sendVerificationNotification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('/email/verify', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
});
