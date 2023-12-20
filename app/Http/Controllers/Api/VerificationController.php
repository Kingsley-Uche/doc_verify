<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;


class VerificationController extends Controller
{
    //

    public function sendVerificationNotification(Request $request){


        if ($request->user()->hasVerifiedEmail()) {
            return response(['message' => 'Email already verified'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response(['message' => 'Verification link sent!'], 202);
    }

public function verify(EmailVerificationRequest $request){
    if ($request->user()->hasVerifiedEmail()) {
        return response(['message' => 'Email already verified'], 200);
    }

    if ($request->user()->markEmailAsVerified()) {
        event(new Verified($request->user()));
    }

    return response(['message' => 'Email verified'], 200);

}
}
