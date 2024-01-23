<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SurchargeModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class surChargeController extends Controller
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
         public function save_surcharge(request $request){

            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'institution_id'=>'required|min:0',
                'institution_charge'=>'required|numeric|between:1,100000.99',
            ]);
            if($validator->fails()){
                return  response()->json(['errors'=>$validator->errors()],422);

            }

            $save_surcharge = SurchargeModel::create(
                ['institution_id'=>strip_tags($request->institution_id),
                'institution_charge' =>strip_tags(strtolower(round($request->institution_charge))),
                'institution_created_admin'=>$user->id,
                ]
            );


            return response()->json(['message'=>'Surcharge added successfully', 'success'=>true, ],201);
         }


         public function update_surcharge(request $request){

            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'institution_id'=>'required|min:0',
                'institution_charge'=>'required|numeric|between:1,100000.99',
            ]);
            if($validator->fails()){
                return  response()->json(['errors'=>$validator->errors()],422);

            }

            $save_surcharge = SurchargeModel::where('id','=',strip_tags($request->id))->update(['institution_id'=>strip_tags($request->institution_id),
                'institution_charge' =>strip_tags(strtolower(round($request->institution_charge))),
                'institution_created_admin'=>$user->id,
                ]
            );


            return response()->json(['message'=>'Surcharge updated successfully', 'success'=>true, ],201);
         }

         public function view_surchage(request $request){


         }
}
