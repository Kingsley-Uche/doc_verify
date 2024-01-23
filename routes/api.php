<?php

use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Institutions;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\VerifierController;
use App\Http\Middleware\SystemManagerMiddleware;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\DocumentsController;
use App\Http\Controllers\Api\SystemAdminController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\ServiceChargeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

//// Public routes
Route::middleware('api')->group(function () {
    Route::post('system/admin/signup',[SystemAdminController::class,'register'])->name('manager.signup');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/countries', [CountriesController::class, 'getAll'])->name('countries.getAll');
    Route::post('/signup', [RegisterController::class, 'register'])->name('signup');
    Route::post('/otp/regenerate', [RegisterController::class, 'regenerateOtp'])->name('otp.regenerate')
                                                                                ->middleware('throttle:1,2');
    Route::post('/confirm_otp', [RegisterController::class, 'validateOtp'])->name('confirmOtp');
    Route::post('/change/password', [RegisterController::class, 'changePassword'])->name('changePassword');
    //routes for system administrator


    Route::post('/system/admin/otp/regenerate', [SystemAdminController::class, 'regenerateOtp'])->name('otp.regenerate.system.admin')
    ->middleware('throttle:1,2');
Route::post('system/admin/confirm/otp', [SystemAdminController::class, 'validateOtp']
)->name('system.admin.confirmOtp');
Route::post('/system/admin/change/password', [SystemAdminController::class, 'changePassword'])->name('system.admin.changePassword');
Route::post('/system/admin/login', [SystemAdminController::class, 'login']
)->name('system.admin.login');
    //implement reset password using otp regenerate
});

// Protected routes
Route::middleware(['api', 'auth:sanctum', 'api.authenticate'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Email verification routes
    Route::post('/email/verification-notification', [VerificationController::class, 'sendVerificationNotification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/email/verify', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // Document verification route
    Route::post('/doc/verify', [DocumentsController::class, 'upload'])->name('doc.verify');
    Route::post('/get/documents', [DocumentsController::class,'view_documents'])->name('get.documents');
    Route::post('/doc/checkout',[PaymentController::class,'checkout'])->name('doc.checkout');
    Route::get('/institutions/all',[Institutions::class,'getAllInstitution'])->name('institutions.all');


    // Add more protected routes as needed
    Route::post('/base/charge/create', [ServiceChargeController::class, 'createServiceCharge'])
    ->name('base.charge');

    Route::post('/verify/institute', [VerifierController::class, 'getOrgByCountry'])
    ->name('base.institute');
   // Route::get('/verifier/by/country',[])

});

Route::group(['middleware' => [SystemManagerMiddleware::class]], function () {



});
