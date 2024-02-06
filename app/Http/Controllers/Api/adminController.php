<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemAdminModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class adminController extends Controller
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
         if (!$user->system_admin_type=='admin_1'|| !$user->system_admin_type=='admin_2') {
             return response()->json(['error' => 'Unauthorized'], 401);
         }

         }




         public function view_all_admin (request $request){
            $user = Auth::user();
           // Check if the user is a system admin
           $admin_all = SystemAdminModel::select('firstName','lastName','email','is_system_admin','system_admin_type','email_verified_at','last_logged_in','created_by_user_id');

         if ($user->is_system_admin) {
            //Is an admin
          $admin_all=  $admin_all->get();


        }
        if($user->system_admin_type=='admin_1'){

            $admin_all=  $admin_all->where('system_admin_type','!=',null)->get();




    }else{

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json(['success' =>true,'data'=>$admin_all], 200);

         }



         public function change_status(request $request){

            $validator = Validator::make($request->all(), [
                'status' => 'required|string',
                'admin_id'=>'required|string',
            ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $status =strip_tags( $validator['status']);
        $id = strip_tags($validator['id']);
        $id = SystemAdminModel::find($id);

        if (!$id) {
            return response()->json(['error' => 'admin not found'], 404);
        }

        $id->where('id','=',$id)->update([
            'status'=>$status,
            'updated_at'=>now(),
        ]);

        return response()->json(['success' =>true,'message'=>'updated'], 200);
         }





        }
