<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/verify/email/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $data =$request->fulfill();
//     dd($data);
//     echo 'verified!';

//     return redirect('/home');
// })->middleware(['auth', 'signed'])->name('verification.verify');
