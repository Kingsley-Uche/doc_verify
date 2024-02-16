<?php

use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Institutions;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\VerifierController;
use App\Http\Middleware\SystemManagerMiddleware;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\DocumentsController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\SystemAdminController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\ServiceChargeController;
use App\Http\Controllers\Api\surChargeController;
use App\Http\Controllers\Api\VerfyingInstitutionController;
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
    Route::post('/doc/initiate/payment',[PaymentController::class,'initiatePayment'])->name('doc.payinit');
    Route::post('/get/doc/by_id',[DocumentsController::class,'get_document_by_id'])->name('doc.get_by_id');

    Route::get('/institutions/all',[Institutions::class,'getAllInstitution'])->name('institutions.all');
    Route::post('get/by/docOwnerId',[DocumentsController::class,'get_by_doc_owner_id'])->name('get.docByOwnerId');
    Route::post('/confirm/transaction',[PaymentController::class,'confirm_payment'])->name('payment.confirm');


    //staff urls for only company owners
    Route::post('/staff/create', [StaffController::class, 'create_staff'])->name('staff.create');
    Route::post('/staff/get/all', [StaffController::class, 'get_all_staff'])->name('staff.get_all');
    Route::post('staff/delete', [StaffController::class, 'delete_staff'])->name('staff.delete');
    Route::post('staff/update', [StaffController::class, 'update_staff'])->name('staff.update');
    Route::post('monitor/document', [ReferralController::class, 'monitor_document'])->name('document.monitor');


    //These are only accessible to the system admin
    Route::post('system/admin/base/charge/create', [ServiceChargeController::class, 'createServiceCharge'])
    ->name('base.charge');
    Route::post('system/admin/base/charge/get', [ServiceChargeController::class, 'view_service_charge'])
    ->name('view.charge');
    //Create service Charge

    Route::post('system/admin/base/charge/update', [ServiceChargeController::class, 'single_edit'])
    ->name('view.edit');
//create surcharge

Route::post('system/admin/surcharge/create', [surChargeController::class, 'save_surcharge'])
    ->name('surcharge.create');
    //update surcharge
    Route::post('system/admin/surcharge/update', [surChargeController::class, 'update_surcharge'])
    ->name('surcharge.update');

    //
    Route::post('system/admin/surcharge/view', [surChargeController::class,'view_surchage'])
    ->name('surcharge.view');
//view all surcharge or a particular surcharge

    Route::post('system/admin/get/org/by/country', [VerifierController::class, 'getOrgByCountry'])
    ->name('country.company');
    //get an organization by country

    Route::post('system/admin/get/inst/by/country', [VerifierController::class, 'getInstByCountry'])
    ->name('country.institute');
    //
    Route::post('system/admin/get/all/companies', [VerifierController::class, 'get_all_companies'])
    ->name('companies.all');

    Route::post('system/admin/get/all/inst', [VerifierController::class, 'get_all_institutions'])
    ->name('institutes.all');

    Route::post('system/admin/add/verifier/institute', [VerfyingInstitutionController::class, 'register_verifier'])
    ->name('register.verify.institute');
    Route::post('system/admin/verify/institute', [VerifierController::class, 'verify_institute'])
    ->name('verify.institute');
    Route::get('system/admin/view/verifier/institution', [VerifierController::class, 'view_verified_institution'])
    ->name('verified.institutes');

    Route::get('system/admin/get/all/documents', [VerifierController::class, 'get_all_documents'])
    ->name('view.documents');

    Route::post('system/admin/get/single/document', [VerifierController::class, 'get_document_by_id'])
    ->name('view.documents.id');

    Route::get('system/admin/verify/document', [VerifierController::class, 'verify_document'])
    ->name('verify.document');
    Route::post('system/admin/update/document', [VerifierController::class,'docUpdate'])->name('update.document');

    Route::post('system/admin/create/admin', [SystemAdminController::class, 'create_admin'])
    ->name('create.admin');
    Route::get('system/admin/view/admin', [SystemAdminController::class, 'view_all_admin'])
    ->name('view.admin');

Route::group(['middleware' => [SystemManagerMiddleware::class]], function () {



});
}
);
