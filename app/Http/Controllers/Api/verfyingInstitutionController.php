<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\company;
use App\Models\category_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\verifier_institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class VerfyingInstitutionController extends Controller
{
    //
    public function __construct() {
        // $this->middleware('auth');
        $user = Auth::user();

         // Check if the user is authenticated
         if (!$user) {
             return response()->json(['error' => 'Unauthorized'], 401);
         }

         // Check if the user is a system admin
         if (!$user->is_system_admin) {
             return response()->json(['error' => 'Unauthorized'], 401);
         }

         }




         public function register_verifier(Request $request){



            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string',
                'lastName' => 'string|required',
                'phone' =>  ['regex:/^([0-9\s\-\+\(\)]*)$/'],
                'email' => 'required|email|unique:users',
                'category'=>'required|string',
                'countryName'=>'required|string',
                'countryCode'=>'required|string',
                'instName'=>'required|string',
                'instId'=>'required|string',
                'industry'=>'required|string',
                'inst_acronym'=>'required|string|max:12',

            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
$data =$request->all();

$institution_ref = strtolower( strip_tags($data['instName']).'/'.substr(md5(uniqid(rand(),true)),0,8),);

$verifier= verifier_institution::create([
    'institution_name' =>strtolower(strip_tags($data['instName'])),
   'institution_id'=>strip_tags($data['instId']),
    'inst_ref'=>$institution_ref,
    'country_code' =>strtolower(strip_tags($data['countryCode'])),
    'country_name' =>strtolower(strip_tags($data['countryName'])),
    'registered_by_admin_id'=>Auth::user()->id,
    'verifier_status'=>'inactive',
    'contact_user_id'=>null
]);


            $user = user::create([
                'firstName'=>strip_tags($data['firstName']),
                'lastName'=>strip_tags($data['lastName']),
                'phone'=>strip_tags($data['phone']),
                'email'=>strip_tags($data['email']),
                'email_verified_at'=>now(),
                'status'=>'active',
                'company_ref'=>$institution_ref,
                'user_company_id'=>$verifier->id,
                'created_by_user_id'=>Auth::user()->id,
                'category_id'=>null,
                'password'=>Hash::make('123456'),

            ]);


           $category=  category_user::create([
                'category_name'=>$data['category'],
                'category_user_id'=>$user->id,
                'category_company_id'=>$verifier->id,
            ]);

            $verifier->update([
                'contact_user_id'=>$user->id,
            ]);
            $user->update(['category_id'=>$category->id]);

            $success['user']=$user;
            $success['status']= true;
            $success['message']= 'Institution created successfuly';

            return response()->json($success,201);
         }




         public function verify_institute(request $request){
            $user = Auth::user();
dd($user);
              // Check if the user is a system admin or admin level 1
              if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'instId'=>'required|string',

            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }



$updated['verifier_status']= 'verified';

                //verifies an institute
                $id_institute = strip_tags($request->id);
            $institute = verifier_institution::find($id_institute);
            $institute->update($updated);

return response()->json(['success' =>true,'message'=>'Institution verified'], 200);

        }


}
