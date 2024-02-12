<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\category_user;

class StaffController extends Controller
{
    //

    private $user;
    private $user_cat;

    public function __construct() {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Access Denied', 'message' => 'You are not logged in.'], 402);

        }
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
        $user = Auth::user();

        $user_cat = User::join('category_users', 'users.category_id', '=', 'category_users.id')
        ->select('users.*', 'category_name as category')
        ->where('email', $user->email)
        ->first();



        if ($user_cat) {
            // user has a company and can see only his staff
            $all_staff = User::where('user_company_id', '=', $user->user_company_id);
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

        $user = Auth::user();


        $validatedData = $validator->validated();
            $password = Hash::make('123456');
            if($user->user_company_id!==null){
                $staff =User::create([
                    'firstName' => strip_tags($validatedData['firstName']),
                    'lastName' => strip_tags($validatedData['lastName']),
                    'email' => strip_tags($validatedData['email']),
                    'password' => $password,
                    'phone'=>'N/A',
                    'status'=>'inactive',
                    'user_company_id'=>$user->user_company_id,
                    'created_by_user_id'=>$user->id,
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
                    unset($category_id);
                    unset($token);
                    return response()->json($data,201);

            }else{

                return response()->json(['error' => 'Access Denied', 'message' => 'Admin not permitted to access this route'], 402);
            }



    }

    public function delete_staff(Request $request)
{
    $validator = Validator::make($request->all(), [
        'staff_id' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $validatedData = $validator->validated();
    $staffId = strip_tags($validatedData['staff_id']);

    $user = Auth::user();
    $userCompany = $user->user_company_id;

    $staffToDelete = User::where('user_company_id', $userCompany)
                        ->where('id', $staffId)
                        ->first();

    if (!$staffToDelete) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Perform the deletion
    $staffToDelete->delete();

    return response()->json(['message' => 'User deleted successfully']);
}
public function update_staff(Request $request)
{
    $validator = Validator::make($request->all(), [
        'staff_id' => 'required|string|max:255',
        'firstName' => 'nullable|string|max:255',
        'lastName' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'status' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $validator->validated();
    $staffId = strip_tags($data['staff_id']);

    $user = Auth::user();
    $userCompany = $user->user_company_id;

    $staffToUpdate = User::where('user_company_id', $userCompany)
                        ->where('id', $staffId)
                        ->first();

    if (!$staffToUpdate) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Prepare the update data
    $updateData = [
        'firstName' => isset($data['firstName']) ? strip_tags($data['firstName']) : $staffToUpdate->firstName,
        'lastName' => isset($data['lastName']) ? strip_tags($data['lastName']) : $staffToUpdate->lastName,
        'email' => isset($data['email']) ? strip_tags($data['email']) : $staffToUpdate->email,
        'password' => Hash::make('1234567'), // Assuming you always want to update the password
        'phone' => 'N/A',
        'user_company_id' => $user->user_company_id,
        'created_by_user_id' => $user->id,
        'email_verified_at' => now(),
        'company_ref' => null,
        'status' => isset($data['status']) ? strip_tags($data['status']) : $staffToUpdate->status,
    ];

    // Perform the update
    $staffToUpdate->update($updateData);

    return response()->json(['success' => true, 'message' => 'Staff updated successfully'], 200);
}

}
