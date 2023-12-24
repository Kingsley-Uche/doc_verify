<?php

namespace App\Http\Controllers\Api;

use Otp;
use App\Models\User;
use App\Models\company;
use App\Models\category_user;
//use Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Ichtrojan\Otp\Models\Otp as ModelsOtp;
use App\Listeners\SendEmailVerificationNotification;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    private $otp;
    //


public function register(request $request){



    if($request->input('category')==='org'){
        //It is an organization so send it to organization register method
        //return response()->json(['message' => 'valid user'], 200);
       $status= $this->register_org($request);
       return $status;


    }elseif($request->input('category')==='indv'){
        //it is an individual so send it to individual register
        $status =$this->register_indv($request);
        return $status;


    }else{
        return response()->json(['message' => 'Invalid user'], 400);
    }




}






    private function register_org(request $request){


       // dd($request->firstName);

       // strip_tags($input)


       $validator = Validator::make($request->all(), [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'phone' =>  ['regex:/^([0-9\s\-\+\(\)]*)$/'],
        'password'=>'required|min:6|confirmed',
        'category' => 'required|max:5',
        'country'=>'required|numeric|min:1',
        'company_name'=>'required|string|min:1',
        'industry'=>'required|string',

    ]);


    if ($validator->fails()) {

       return  response()->json(['errors'=>$validator->errors()],422);
        //give feedback
    }else{
        $validatedData = $validator->validated();


//save information

 // Validation passed
 //save information for company


 $company= company::create([
            'company_name' =>strtolower(strip_tags($validatedData['company_name'])),
            'company_industry' => strtolower(strip_tags($validatedData['industry'])),
            'company_country_id' =>strtolower(strip_tags($validatedData['country'])),
        ]);






 $user =user::create(
    [
        'firstName'=>strtolower(strip_tags($validatedData['firstName'])),
        'lastName'=>strtolower(strip_tags($validatedData['lastName'])),
        'phone'=>strtolower(strip_tags($validatedData['phone'])),

        'email'=>strtolower(strip_tags($validatedData['email'])),
        'password'=> Hash::make(strip_tags($validatedData['password'])),
        'user_company_id'=>$company->id,
    ],
    );
    //update comapny table to save the user that created the company;
$user_id = user::latest()->first()->id;
$company_id =company::latest()->first()->id;

//the owner of the company
$company->update([
    'company_created_by_user_id'=>$user_id,
]);

$token =$user->createToken('api-token')->plainTextToken;

//Save user in category table
category_user::create([
    'category_name'=>$validatedData['category'],
    'category_user_id'=>$user_id,
]);
$category_id =category_user::latest()->first()->id;

$user->update(['category_id'=>$category_id]);
       // $token =$user->createToken('api-token')->plainTextToken;
       $success['token']=$token;
       $success['user']=$user;
       $success['message']="An otp has been sent to your email :".$request->email.'Kindly use it to verify your account';
       $success['success']='true';
       $user->notify(new EmailVerificationNotification);

       return response()->json($success,201);

    }






    }


    private function register_indv(request $request){


        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' =>  ['regex:/^([0-9\s\-\+\(\)]*)$/'],
            'password'=>'required|min:6|confirmed',
            'country'=>'required|numeric|min:1',
        ]);



        if ($validator->fails()) {


           return  response()->json(['errors'=>$validator->errors()],422);





            //give feedback
        }else{
        //validation has passed

            $validatedData = $validator->validated();
            $user =user::create(
                [
                    'firstName'=>strip_tags($request->input('firstName')),
                    'lastName'=>strip_tags($request->input('lastName')),
                    'phone'=>strip_tags ($request->input('phone')),
                    'category'=>strip_tags($request->input('category')),
                    'email'=>strip_tags($request->input('email')),
                    'password'=> Hash::make(strip_tags($request->input('password'))),
                ],
                );


            $token =$user->createToken('api-token')->plainTextToken;





                   // $token =$user->createToken('api-token')->plainTextToken;
                   $success['token']=$token;
                   $success['user']=$user;
                   $success['message']="An otp has been sent to your email :".$request->email.'Kindly use it to verify your account';
                   $success['success']='true';
                   $user->notify(new EmailVerificationNotification);

                   return response()->json($success,201);



        }


    }






    public function validateOtp(request $request){

$otp =new otp;

$otp_status =$otp->validate($request->email, $request->otp);

if(!$otp_status->status){
  return response()->json(['error'=>$otp, 'message'=>'Invalid otp'], 401);
}

$user =User::where('email',$request->email)->first();


if ($user) {
    $time =now();

    User::where('email', $request->email)->update(['email_verified_at' =>now()]);
    $success['success']='true';
    $code =200;
    $message='Your email has been verified. You can login.';
} else {
    // Handle the case when the user is not found
    $success['success']='false';

    $code =400;
    $message ='Invalid OTP';
}



return response()->json(['message'=>$message, 'success'=>$success, 'time'=>$time,],$code);


    }



    public function regenerateOtp(request $request){





        $validator = Validator::make($request->all(), [
            'email' => 'required|email|'],
        );
        $user = User::where('email', strip_tags($request->email))->first();


        if ($validator->fails()) {


            return  response()->json(['errors'=>$validator->errors()],422);





             //give feedback
         }else{
             $validatedData = $validator->validated();

             if ($user) {
                // User exists, so proceed with resending code
                $success['user'] = $user;
                $success['success']=true;
                $success['message'] = "An OTP has been sent to your email: " . $request->email . ' Kindly use it to verify your account';
                $user->notify(new EmailVerificationNotification);
                return response()->json($success, 200);
            } else {
                // User does not exist, return an error response
                return response()->json(['error' => 'Invalid user', 'message' => 'User does not exist', 'success'=>false], 404);
            }




            }








        //


}

public function changePassword(request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|',
        'otp'=>'required|size:6',
        'password'=>'required|min:6|confirmed',]
    );
    $user = User::where('email', strip_tags($request->email))->first();


    if ($validator->fails()) {
        return  response()->json(['errors'=>$validator->errors()],422);
         //give feedback
     }else{



        if ($user) {

            // User exists, so  proceed with changing password;
            //confirm otp;

            $otp =new otp;

$otp_status =$otp->validate($request->email, $request->otp);
if(!$otp_status->status){
  return response()->json(['error'=>'Invalid OTP', 'message'=>'Invalid otp', 'success'=>false], 401);
}



if ($user) {
    $time =now();

    User::where('email', $request->email)->update(['email_verified_at' =>now(),'password' =>Hash::make(strip_tags($request->input('password')))]);
     $success['user'] = $user;
     $success['success']=true;
     $success['message'] = "Your password has been changed successfully. Kindly login with your new password";

            return response()->json($success, 200);

} else {
    // Handle the case when the user is not found
    $success['success']='false';

    $code =400;
    $message ='Invalid OTP';
}
        } else {
            // User does not exist, return an error response
            return response()->json(['error' => 'Invalid user', 'message' => 'User does not exist', 'success'=>false], 404);
        }

}

}



}
