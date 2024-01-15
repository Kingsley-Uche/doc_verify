<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\serviceCharge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceChargeController extends Controller
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
    public function createServiceCharge(request $request){

        $validator = Validator::make($request->all(), [
            'docCateg' => 'required|array|max:255',
            'docCateg.*' => 'required|string|max:255',
            'baseCharge.*' => 'required|array',
            'baseCharge.*' => 'required|numeric|between:1,100000.99',
            'category_user'=>'required|string',//This was put diffrent because there might lower charge for organizations
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
        $doc =$request->input('docCateg');
        $base_charge = $request->input('baseCharge');
        $category_user = $request->input('category_user');

        $i =0;
        foreach($doc as $doc_cat){
        $doc_category = $doc_cat;
        $doc_charge = $base_charge[$i];
        $category_user =$category_user;
        $this->save_charge($doc_category,$doc_charge,$category_user);
$i++;
        }



        return response()->json(['success' =>true,'message'=>'Base charge created successfully'], 201);


    }


}

private function save_charge($doc_category,$doc_charge,$category_user){

    serviceCharge::create([
'doc_cat'=>strip_tags(strtolower($doc_category)),
'doc_charge'=>strip_tags(strtolower(round($doc_charge, 2))),
'category_user'=>strip_tags(strtolower($category_user)),
'created_admin_id'=>Auth::user()->id,




    ]);


}



}
