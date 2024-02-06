<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\category_user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    private $user;
    private $user_cat;

    public function __construct() {
        // only accessible to system admin, admin_1, admin_2, or user that has a company
        $user = Auth::user();
        $user_cat = User::join('category_users', 'users.category_id', '=', 'category_users.id')
            ->select('users.*', 'category_name as category')
            ->where('email', $user->email)
            ->first();

        $this->user = $user;
        $this->user_cat = $user_cat;

        if (!$user->is_system_admin && ($user->system_admin_type != 'admin_1' && $user->system_admin_type != 'admin_2') && $user_cat->category != 'org') {
            return response()->json(['error' => 'Access Denied', 'message' => 'You are not permitted to access this route'], 402);
        }
    }

    public function get_all_staff(Request $request){
        if ($this->user_cat) {
            // user has a company and can see only his staff
            $all_staff = User::where('user_company_id', '=', $this->user->user_company_id);
        } else {
            $all_staff = User::select('firstName', 'lastName', 'phone', 'company_ref', 'email');
        }

        $all_staff = $all_staff->get();

        return response()->json(['success' => true, 'data' => $all_staff], 200);
    }

    public function create_staff(request $request){


        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $validatedData = $validator->validated();
            $password = Hash::make('123456');
            if($this->user->user_company_id!==null){
                $staff =User::create([
                    'firstName' => strip_tags($validatedData['firstName']),
                    'lastName' => strip_tags($validatedData['lastName']),
                    'email' => strip_tags($validatedData['email']),
                    'password' => $password,
                    'user_company_id'=>$this->user->user_company_id,
                    'created_by_user_id'=>$this->user->id,
                    'email_verified_at'=>now(),
                    'company_ref'=>null,
                ]);


                $user_id = user::latest()->first()->id;

                    category_user::create([
                        'category_name'=>'staff',
                        'category_user_id'=>$user_id,
                    ]);
                    $category_id =category_user::latest()->first()->id;

                    $staff->update(['category_id'=>$category_id]);
                $token =$staff->createToken('api-token')->plainTextToken;

                    $data['token']=$token;
                    $data['user']=$staff;
                    $data['success']= true;
                    return response()->json($data,201);

            }else{

                return response()->json(['error' => 'Access Denied', 'message' => 'Admin not permitted to access this route'], 402);
            }



    }
}
