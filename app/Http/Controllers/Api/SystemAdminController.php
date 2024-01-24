<?php

namespace App\Http\Controllers\api;
use Otp;
use Illuminate\Http\Request;
use App\Models\SystemAdminModel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;


//use Illuminate\Auth\Events\Registered;//sends email link
//use App\Notifications\AdminEmailVerification;

class SystemAdminController extends Controller
{

    private $otp;

// public function __construct()
// {
// $this->middleware('auth::system_admins');
// }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:system_admin',
            'phone' => ['regex:/^([0-9\s\-\+\(\)]*)$/'],
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $validatedData = $validator->validated();
            $password = Hash::make($request->password);

            $systemAdmin = SystemAdminModel::create([
                'firstName' => strip_tags($validatedData['firstName']),
                'lastName' => strip_tags($validatedData['lastName']),
                'email' => strip_tags($validatedData['email']),
                'phone' => strip_tags($validatedData['phone']),
                'password' => $password,
                'is_system_admin'=>true,
            ]);
            $token =$systemAdmin->createToken('api-token')->plainTextToken;
            // Send email verification notification
            //$systemAdmin->notify(new AdminEmailVerification);
            $success['token']=$token;
            $success['user']=$systemAdmin;
            $success['message']="An otp has been sent to your email :".$request->email.'Kindly use it to verify your account';
            $success['success']=true;
            $systemAdmin->notify(new EmailVerificationNotification);


unset($systemAdmin);
            return response()->json([$success, ], 201);
        }
    }




    public function validateOtp(request $request){
        $time ='';

        $otp =new otp;

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|',
            'otp'=>'required|string|min:6',]
        );
        if ($validator->fails()) {




            return  response()->json(['errors'=>$validator->errors()],422);





             //give feedback
         };
        $otp_status =$otp->validate($request->email, $request->otp);

        if(!$otp_status->status){
          return response()->json(['error'=>'invalid otp', 'message'=>'Invalid otp'], 401);
        }

        $systemAdmin =SystemAdminModel::where('email',$request->email)->first();


        if ($systemAdmin) {
            $time =now();

            SystemAdminModel::where('email', $request->email)->update(['email_verified_at' =>now()]);
            $success['success']=true;
            $success['message']='Your email has been verified';
            $code =201;
            $message='Your email has been verified. You can login.';
            return response()->json([$success], $code);
        } else {
            // Handle the case when the user is not found
            $success['success']=false;

            $code =400;
            $message ='Invalid OTP';
        }



        return response()->json(['message'=>$message, $success, 'time'=>$time,],$code);


            }



            public function regenerateOtp(request $request){





                $validator = Validator::make($request->all(), [
                    'email' => 'required|email|'],
                );
                $systemAdmin = SystemAdminModel::where('email', strip_tags($request->email))->first();


                if ($validator->fails()) {


                    return  response()->json(['errors'=>$validator->errors()],422);





                     //give feedback
                 }else{
                     $validatedData = $validator->validated();

                     if ($systemAdmin) {
                        // User exists, so proceed with resending code
                        $success['user'] = $systemAdmin;
                        $success['success']=true;
                        $success['message'] = "An OTP has been sent to your email: " . $request->email . ' Kindly use it to verify your account';
                        $systemAdmin->notify(new EmailVerificationNotification);
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
            $systemAdmin = SystemAdminModel::where('email', strip_tags($request->email))->first();


            if ($validator->fails()) {
                return  response()->json(['errors'=>$validator->errors()],422);
                 //give feedback
             }else{



                if ($systemAdmin) {

                    // User exists, so  proceed with changing password;
                    //confirm otp;

                    $otp =new otp;

        $otp_status =$otp->validate($request->email, $request->otp);
        if(!$otp_status->status){
          return response()->json(['error'=>'Invalid OTP', 'message'=>'Invalid otp', 'success'=>false], 401);
        }



        if ($systemAdmin) {
            $time =now();

            SystemAdminModel::where('email', $request->email)->update(['email_verified_at' =>now(),'password' =>Hash::make(strip_tags($request->input('password')))]);
             $success['user'] = $systemAdmin;
             $success['success']=true;
             $success['message'] = "Your password has been changed successfully. Kindly login with your new password";

                    return response()->json($success, 200);

        } else {
            // Handle the case when the user is not found
            $success['success']=false;

            $code =400;
            $message ='Invalid OTP';
        }
                } else {
                    // User does not exist, return an error response
                    return response()->json(['error' => 'Invalid user', 'message' => 'User does not exist', 'success'=>false], 404);
                }

        }

        }


        public function login(Request $request)
        {
            $credentials = $request->only('email', 'password');
            $userType = $request->input('user_type', 'web'); // Default to 'web' guard




            if (Auth::guard('system_admin')->attempt($credentials)) {
                $user = Auth::guard('system_admin')->user();
                $user->last_logged_in= now();
                $user->save();
                $token = $user->createToken('token-name');

                return response()->json(['token' => $token->plainTextToken, 'user'=>$user]);
            } else {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        }



        public function create(request $request){
            if (Auth::check()) {
                // Get the authenticated user
                $user = Auth::user();


                if ($user->is_system_admin) {
                    // The user is a system admin, proceed with the activity

                } else {
                    // The user is not a system admin, deny the activity
                    return response()->json(['error' => 'Permission denied. User is not a system admin'], 403);
                }
            } else {
                // User is not authenticated, handle accordingly
                return response()->json(['error' => 'Unauthorized'], 401);
            }

        }


}
