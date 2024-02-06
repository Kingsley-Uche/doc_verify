<?php

namespace App\Http\Controllers\Api;

use App\Models\company;
//use App\Http\Middleware\SystemManagerMiddleware;
use App\Models\institutions;
use Illuminate\Http\Request;
use App\Models\document_owner;
use App\Models\FinancialDocuments;
use App\Http\Controllers\Controller;
use App\Models\EducationalDocuments;
use App\Models\verifier_institution;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalDocuments;
use Illuminate\Support\Facades\Validator;

class VerifierController extends Controller
{

    public function __construct() {
        // $this->middleware('auth');
        $user = Auth::user();

         // Check if the user is authenticated
         if (!$user) {
             return response()->json(['error' => 'Unauthorized'], 401);
         }



         }



    public function getOrgByCountry(request $request){

        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $country = strip_tags($request->country_code);
        $companies = company::select('company_name','company_industry','company_ref')->where('company_country_id', '=',$country )->get();
        return response()->json(['success' =>true,'data'=>$companies], 200);

    }

    public function verify_institute(request $request){
        $user = Auth::user();

          // Check if the user is a system admin or admin level 1
          if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }




        $type_org = $request->type;
        if($type_org==='org'){
            //verifies an organization
            $id_company = strip_tags($request->id);
            $institute = company::where('id','=',$id_company)->get();
            verifier_institution::create([
                'company_id'=>$id_company,
                'verified_admin_id'=>$user->id,
                'verifier_status'=>'verified',
                'created_at'=>now(),
                'updated_at'=>now(),

            ]);


        }elseif($type_org==='inst'){
            //verifies an institute
            $id_institute = strip_tags($request->id);
        $institute = institutions::where('id','=',$id_institute)->get();
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
    public function get_all_companies(){
        $companies =company::select('company_name', 'company_industry','company_country_id','company_ref','company_created_by_user_id','updated_at')->get();
        return response()->json(['success' =>true,'data'=>$companies], 200);

    }

    public function get_all_institutions(){
        $institution =institutions::select('id', 'name')->get();
        return response()->json(['success' =>true,'data'=>$institution], 200);

    }

    public function getInstByCountry(request $request){

     $validator = Validator::make($request->all(), [
        'country_code' => 'required|string',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
        $country = strip_tags($request->country_code);
        $institution = institutions::select('id','name')->where('country_id', '=',$country )->get();
        return response()->json(['success' =>true,'data'=>$institution], 200);

    }
    public function view_verified_institution(){
 $verified_institutions = verifier_institution::select('name', 'institution_id', 'verifier_institutions.company_id', 'verified_admin_id', 'verifier_status')
    ->leftJoin('institutions', 'institutions.id', '=', 'verifier_institutions.institution_id')
    ->leftJoin('companies', 'companies.id', '=', 'verifier_institutions.institution_id')
    ->where('verifier_institutions.verifier_status', '=', "verified")
    ->get();

    return response()->json(['success' =>true,'data'=>$verified_institutions], 200);


    }
    public function get_all_documents(request $request){
        if($request->type){
            $validator = Validator::make($request->all(), [
            'type'=> 'required|string',

        ]);
        $type = strip_tags($request->type);

           if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }




        }else{
            $type =null;
        }






    $user2verify = document_owner::select('id','docOwnerFirstName','docOwnerMiddleName','docOwnerLastName','docOwnerDOB','uploaded_by_user_id')->get();
    //->paginate(10);


    $response = [];
    $info =[];



    foreach ($user2verify as $user => $value) {
    $documentSet = [
        'educationalDocuments' => $this->getEducationalDocuments($value->id, $type, $value),
        'professionalDocuments' => $this->getProfessionalDocuments($value->id, $type, $value),
        'financialDocuments' => $this->getFinancialDocuments($value->id, $type, $value),
    ];

    if (
        count($documentSet['educationalDocuments']) > 0 ||
        count($documentSet['professionalDocuments']) > 0 ||
        count($documentSet['financialDocuments']) > 0
    ) {



        $info['user']['documents'] = $documentSet;
        $info['user']['info'] = $value;
        $response[] = $info;

       unset($documentSet);

    }

}
 return $response;

    return $response;
}


    private function getEducationalDocuments($docOwnerId, $type =null){
        $query = EducationalDocuments::where('doc_owner_id', $docOwnerId);

        if ($type !== null) {
            $query->where('status', $type);
        }

        $educational_files = $query->get();

        return $educational_files;

    }

    private function getProfessionalDocuments($docOwnerId, $type=null){

        $query = ProfessionalDocuments::where('doc_owner_id', $docOwnerId);

        if ($type !== null) {
            $query->where('status', $type);
        }

        $professional_files = $query->get();

        return $professional_files;

    }
    private function getFinancialDocuments($docOwnerId, $type =null){

        $query = FinancialDocuments::where('doc_owner_id', $docOwnerId);

        if ($type !== null) {
            $query->where('status', $type);
        }

        $financial_files = $query->get();

        return $financial_files;

    }




public function verify_document(request $request){
    $user = Auth::user();

    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $validator = Validator::make($request->all(), [
        'type' => 'required|string',
        'id' => 'string|required',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }



    $type =strip_tags($request->type);
    $doc_id = strip_tags($request->id);


    switch ($type) {
        case 'educ':
            $this->verify_educational_document($type, $doc_id);
            break;
        case 'prof':
            $this->verify_professional_document($type, $doc_id);
            break;
        case 'finance':

            $this->verify_financial_document($type,$doc_id);
            break;

    }



}

private function verify_educational_document($type, $id){
    //this verifies educational document
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $document = EducationalDocuments::find($id);

if ($document) {
    $document->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);
}

return response()->json(['success' =>true,'message'=>'Document verified'], 200);

}

private function verify_professional_document($type, $id){
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    //This verifies professional document

    $document = ProfessionalDocuments::find($id);

if ($document) {
    $document->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);
}

return response()->json(['success' =>true,'message'=>'Document verified'], 200);




}

private function verify_financial_document($type,$id){
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    //This verifies professional document
    $document = FinancialDocuments::find($id);

if ($document) {
    $document->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);
}

return response()->json(['success' =>true,'message'=>'Document verified'], 200);


}


private function batch_verify_educational(array $arrayOfIds){

    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    EducationalDocuments::whereIn('id', $arrayOfIds)
    ->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);

    return response()->json(['success' =>true,'message'=>'Documents verified'], 200);
}


private function batch_verify_professional(array $arrayOfIds){
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    ProfessionalDocuments::whereIn('id', $arrayOfIds)
    ->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);
    return response()->json(['success' =>true,'message'=>'Documents verified'], 200);
}

private function batch_verify_financial(array $arrayOfIds){

    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    FinancialDocuments::whereIn('id', $arrayOfIds)
    ->update([
        'status' => 'verified',
        'updated_at' => now(),
    ]);
    return response()->json(['success' =>true,'message'=>'Documents verified'], 200);

}

}

