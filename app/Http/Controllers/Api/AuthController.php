<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //

          public function logout(Request $request){
            $this->middleware(['auth','verified']);

            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        }




    public function login(request $request){

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');




            // Check if the user's email is verified
            $user  =  user::join('category_users', 'users.category_id','=','category_users.id')
                            ->select('users.*', 'category_name as category',)
                           -> where('email', $credentials['email'])->first();


            //$user = User::where('email', $credentials['email'])->first();

            if ($user && $user->email) {
                // User's email is verified, attempt authentication
                if($user->email_verified_at){




                     if (Auth::attempt($credentials)) {
                        if($user->category=='org'){
                           $user->company =company::where('company_created_by_user_id', $user->id)->first();

                        }
                   $user =$user;
                    $token = $user->createToken('api-token')->plainTextToken;

                    return response()->json(['token' => $token, 'user' => $user, 'message' => 'You have logged in successfully', 'success'=>true], 201);
                } else {
                    // Authentication failed
                    return response()->json(['error'=>'invalid email or password','message' => 'Invalid email or password'], 401);
                }

                }else{
                     return response()->json(['error'=>'email not verified', 'message' => 'Email not verified'], 401);
                }

            } else {
                // User's email is not verified
                return response()->json(['error'=>'Login failed', 'message' => 'Invalid username or password'], 401);
            }



        }












}
