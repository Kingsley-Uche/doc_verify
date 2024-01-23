<?php

namespace App\Http\Controllers\Api;

use App\Models\company;
//use App\Http\Middleware\SystemManagerMiddleware;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\institutions;
use App\Models\verifier_institution;
use Illuminate\Support\Facades\Auth;

class VerifierController extends Controller
{

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



    public function getOrgByCountry(request $request){
        $country = strip_tags($request->country_code);
        $companies = company::select('company_name','company_industry','company_ref')->where('company_country_id', '=',$country )->get();
        return response()->json(['success' =>true,'data'=>$companies], 200);

    }

    public function verify_institute(request $request){
        $user = Auth::user();
        $type_org = $request->type;
        if($type_org==='org'){
            //verifies an organization
            $id_company = strip_tags($request->id());
            $institute = company::where('id','=',$id_company)->find();
            verifier_institution::create([
                'institution_id'=>$id_company,
                'verified_admin_id'=>$user->id,
                'verifier_status'=>'verified',
                'created_at'=>now(),
                'updated_at'=>now(),

            ]);


        }elseif($type_org==='inst'){
            //verifies an institute
            $id_institute = strip_tags($request->id());
        $institute = institutions::where('id','=',$id_institute)->find();
        verifier_institution::create([
            'institution_id'=>$id_institute,
            'verified_admin_id'=>$user->id,
            'verifier_status'=>'verified',
            'created_at'=>now(),
            'updated_at'=>now(),

        ]);




        }

        //handles verification of institutes after signup

        return response()->json(['success' =>true,'message'=>'Institution verified'], 200);

    }
}
