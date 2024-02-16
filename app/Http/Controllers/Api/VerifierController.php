<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
//use App\Http\Middleware\SystemManagerMiddleware;
use App\Models\company;
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

          if (!$user->is_system_admin||!$user->system_admin_type='admin_1') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
 if(isset($request->type)||$request->type!=null){
    $validator = Validator::make($request->all(), [
        'type' => 'required|string',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }


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

        // Assuming your model name is Company (starting with a capital letter as per convention)
  $companies = Company::select(
      'firstName', // Assuming these fields are in the 'users' table, you'll need to prefix them with 'users.'
      'lastName',
      'phone',
      'email',
      'companies.company_name',
      'companies.company_industry',
      'companies.company_ref',
      'companies.company_created_by_user_id',
      'companies.updated_at as dateOfCompanyUpdate',
      'country.id as CountryCode',
      'country.name as CountryName'
  )
  ->leftJoin('users', 'users.id', '=', 'companies.company_created_by_user_id')->leftJoin('country', 'country.id','=', 'company_country_id')
  ->get();

  return response()->json(['success' => true, 'data' => $companies], 200);


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


    public function view_verified_institution(request $request){
        $data = $request->all();

        $query = verifier_institution::query();

        if (isset($data['type']) && $data['type'] !== null) {
            $type = strip_tags($data['type']);
            $query->where('verifier_status', '=', $type);
        }
        $verified_institutions = $query->get();

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



        $info['documents'] = $documentSet;
        $info['doc_owner'] = $value;
        $response[] = $info;

       unset($documentSet);

    }

}
 return $response;

    return $response;
}


public function get_document_by_id(request $request){

    $user = Auth::user();



    $validator = Validator::make($request->all(), [
        'docOwnerId' => 'required|string',
        'doc_category'=>'required|string',
        'doc_id'=>'required|string',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }



    $cat =strip_tags($request->doc_category);
    $doc_id = strip_tags($request->doc_id);
    $docOwnerId = strip_tags($request->docOwnerId);


    switch ($cat) {

        case 'educ':
           $data['document']=  $this->getEducationalDocuments($docOwnerId, $type=null,$value=null, $doc_id);
            break;
        case 'prof':
           $data['document'] = $this->getProfessionalDocuments($docOwnerId, $type=null,$value=null, $doc_id);
            break;
        case 'finance':

            $data['document'] = $this->getFinancialDocuments($docOwnerId, $type=null,$value=null, $doc_id);
            break;

    }

    if(isset($data)&&$data!==null){
        $uploaded_user_id =$data['document']->first()->uploaded_by_user_id;
        $flag = 'yes';

   }
   if($flag==='yes'){
    $data['uploaded_by_user']= User::where('id','=',$uploaded_user_id)->first();

   }



    $data['owner']= document_owner::where('id', '=', $docOwnerId)->first();

    return response()->json(['success' =>true,'data'=>$data], 200);

  }


    private function getEducationalDocuments($docOwnerId,$type, $value, $doc_id=null){


        $query = EducationalDocuments::where('doc_owner_id', $docOwnerId)
        ->where(function ($query) use ($type,$doc_id) {
            if (isset($type)||$type!= null) {
                $query->where('status', $type);
            }

            if(isset($docOwnerId)&&$docOwnerId!=null ){
                $query->Where('doc_owner_id', $docOwnerId);



            }
            if(isset($doc_id)&& $doc_id!=null){
                $query->Where('id','=',$doc_id);
            }




        });

        $educational_files = $query->get();


        return $educational_files;

    }

    private function getProfessionalDocuments($docOwnerId,$type, $value, $doc_id=null){


        $query = ProfessionalDocuments::where('doc_owner_id', $docOwnerId)
        ->where(function ($query) use ($type,$doc_id) {
            if (isset($type)||$type!= null) {
                $query->where('status', $type);
            }

            if(isset($docOwnerId)&&$docOwnerId!=null ){
                $query->Where('doc_owner_id', $docOwnerId);



            }
            if(isset($doc_id)&& $doc_id!=null){
                $query->Where('id','=',$doc_id);
            }




        });

        $educational_files = $query->get();


        return $educational_files;

    }
    private function getFinancialDocuments($docOwnerId,$type, $value, $doc_id=null){


        $query = FinancialDocuments::where('doc_owner_id', $docOwnerId)
        ->where(function ($query) use ($type,$doc_id) {
            if (isset($type)||$type!= null) {
                $query->where('status', $type);
            }

            if(isset($docOwnerId)&&$docOwnerId!=null ){
                $query->Where('doc_owner_id', $docOwnerId);



            }
            if(isset($doc_id)&& $doc_id!=null){
                $query->Where('id','=',$doc_id);
            }




        });

        $educational_files = $query->get();


        return $educational_files;


    }




public function verify_document(request $request){
    $user = Auth::user();

    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $validator = Validator::make($request->all(), [
        'type' => 'required|string',
        'id' => 'string|required',
        'verify_info'=>'string|required|max:1200',
        'status' => 'required|in:pending,verified,queried,archived',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }



    $type =strip_tags($request->type);
    $doc_id = strip_tags($request->id);
    $status =strip_tags($request->status);


    switch ($type) {
        case 'educ':
            $this->verify_educational_document($type, $doc_id,$status);
            break;
        case 'prof':
            $this->verify_professional_document($type, $doc_id,$status);
            break;
        case 'finance':

            $this->verify_financial_document($type,$doc_id,$status);
            break;

    }



}

private function verify_educational_document($type, $id,$status){
    //this verifies educational document
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $document = EducationalDocuments::find($id);

if ($document) {
    $document->update([
        'status' => $status,
        'updated_at' => now(),
    ]);
}

return response()->json(['success' =>true,'message'=>'Document verified'], 200);

}

private function verify_professional_document($type, $id,$status){
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    //This verifies professional document

    $document = ProfessionalDocuments::find($id);

if ($document) {
    $document->update([
        'status' => $status,
        'updated_at' => now(),
    ]);
}

return response()->json(['success' =>true,'message'=>'Document verified'], 200);




}

private function verify_financial_document($type,$id,$status){
    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    //This verifies professional document
    $document = FinancialDocuments::find($id);

if ($document) {
    $document->update([
        'status' => $status,
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


private function update_document($type, $doc_id){

    $user = Auth::user();
    if (!$user->is_system_admin||!$user->system_admin_type=='admin_1') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $doc_type = strip_tags($type);
    $doc_id = strip_tags($type);
    switch ($doc_type) {

        case 'educ':
           $data['document']=  $this->updateEducationalDocuments($doc_id);
            break;
        case 'prof':
           $data['document'] = $this->updateProfessionalDocuments($doc_id);
            break;
        case 'finance':

            $data['document'] = $this->updateFinancialDocuments($doc_id);
            break;

    }



}
private function updateDocOwner($record, $data){




    $owner = document_owner::find(strip_tags($data['docOwnerId']));
    if($owner){
        $owner->update($record);
    }
    unset($data);
    unset($owner);
    return true;


}

private function updateEducationalDocument($data){
    $doc_id = strip_tags($data['doc_id']);
    $doc_owner_id = strip_tags($data['docOwnerId']);
    if (isset($data['file'])) {
        $validator = Validator::make($data, [
            'file' => 'required|mimes:pdf|max:2048', // Validate the 'file'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Only PDF files are allowed and size must not exceed 2MB'], 422);
        }


        $educ = EducationalDocuments::join('document_owners', 'document_owners.id', '=', 'educational_documents.doc_owner_id')
    ->where('educational_documents.id', $doc_id) // Specify the table for clarity
    ->where('document_owners.id', $doc_owner_id) // This assumes you meant to filter by the owner's ID explicitly
    ->first();

        if ($educ && $educ->doc_path) {
            $educ_path = public_path($educ->doc_path);

            if (file_exists($educ_path)) {
                unlink($educ_path); // Delete the file
            }
$name = $educ->docOwnerFirstName;
$type = $educ->doc_type;

        }
        $file = $data['file'];
        $ext = $file->getClientOriginalExtension();
        $newFileName = time() . '_' . 'updated' . '/'.$name;
        $path = 'uploads/docs/' . $newFileName . "_$type" . '.' . $ext;
        $file->move(public_path('uploads/docs'), $path);

        // Continue with any other logic after file deletion
    }




    $updateData = [
        'doc_type' => 'educ',
        'status' => 'submitted',
        'updated_at' => now(),
        'uploaded_by_user_id' => Auth::user()->id,
        'doc_owner_id'=>strip_tags($doc_owner_id),
    ];


    if (isset($data['courseOrSubject'])) {
        $updateData['course'] = strip_tags($data['courseOrSubject']);
    }

    if (isset($data['schoolCountryEduc'])) {
        $updateData['country_code'] = strip_tags($data['schoolCountryEduc']);
    }

    if (isset($data['matricNumber'])) {
        $updateData['studentId'] = strip_tags($data['matricNumber']);
    }

    if (isset($data['examBoard'])) {
        $updateData['exam_board'] = strip_tags($data['examBoard']);
    }

    if (isset($data['schoolNameEduc'])) {
        $updateData['verifier_name'] = strip_tags($data['schoolNameEduc']);
    }

    if (isset($data['schoolCity'])) {
        $updateData['verifier_city'] = strip_tags($data['schoolCity']);
    }

    if (isset($data['enrollmentYearEduc'])) {
        $updateData['start_year'] = strip_tags($data['enrollmentYearEduc']);
    }

    if (isset($data['graduationYearEduc'])) {
        $updateData['end_year'] = strip_tags($data['graduationYearEduc']);
    }

    if (isset($data['addInfo'])) {
        $updateData['doc_info'] = strip_tags($data['addInfo']);
    }

    if (isset($path)) {
        $updateData['doc_path'] = $path;
    }

    //update in the database

    $educ->where('id', '=', $doc_id)->update($updateData);
    return true;






}

private function updateProfessionalDocuments($data){


    $doc_id = strip_tags($data['doc_id']);
    $doc_owner_id = strip_tags($data['docOwnerId']);
    if (isset($data['file'])) {
        $validator = Validator::make($data, [
            'file' => 'required|mimes:pdf|max:2048', // Validate the 'file'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Only PDF files are allowed and size must not exceed 2MB'], 422);
        }


        $prof = ProfessionalDocuments::join('document_owners', 'document_owners.id', '=', 'professional_documents.doc_owner_id')
    ->where('professional_documents.id', $doc_id) // Specify the table for clarity
    ->where('document_owners.id', $doc_owner_id) // This assumes you meant to filter by the owner's ID explicitly
    ->first();

        if ($prof && $prof->doc_path) {
            $prof_path = public_path($prof->doc_path);

            if (file_exists($prof_path)) {
                unlink($prof_path); // Delete the file
            }
$name = $prof->docOwnerFirstName;
$type = $prof->doc_type;

        }
        $file = $data['file'];
        $ext = $file->getClientOriginalExtension();
        $newFileName = time() . '_' . 'updated' . '/'.$name;
        $path = 'uploads/docs/' . $newFileName . "_$type" . '.' . $ext;
        $file->move(public_path('uploads/docs'), $path);

        // Continue with any other logic after file deletion
    }




    $updateData = [
        'doc_type' => 'prof',
        'status' => 'submitted',
        'updated_at' => now(),
        'uploaded_by_user_id' => Auth::user()->id,
        'doc_owner_id'=>strip_tags($doc_owner_id),
    ];




    if (isset($data['studentIdProf'])) {
        $updateData['studentIdProf'] = strip_tags($data['studentId']);
    }

    if (isset($data['qualificationProf'])) {
        $updateData['qualification'] = strip_tags($data['qualificationProf']);
    }

    if (isset($data['enrolmentStatusProf'])) {
        $updateData['enrollment_status'] = strip_tags($data['enrolmentStatusProf']);
    }

    if (isset($data['schoolNameProf'])) {
        $updateData['doc_verifier_name'] = strip_tags($data['schoolNameProf']);
    }

    if (isset($data['schoolCountryProf'])) {
        $updateData['country_code'] = strip_tags($data['schoolCountryProf']);
    }

    if (isset($data['profCourse'])) {
        $updateData['course'] = strip_tags($data['profCourse']);
    }

    if (isset($data['enrollmentYearProf'])) {
        $updateData['start_year'] = strip_tags($data['enrollmentYearProf']);
    }

    if (isset($data['graduationYearProf'])) {
        $updateData['end_year'] = strip_tags($data['graduationYearProf']);
    }

    if (isset($data['addInfoProf'])) {
        $updateData['add_info'] = strip_tags($data['addInfo']);
    }

    if (isset($path)) {
        $updateData['doc_path'] = $path;
    }


    $prof->where('id', '=', $doc_id)->update($updateData);

    return true;

}

private function updateFinancialDocument($data){

    $doc_id = strip_tags($data['doc_id']);
    $doc_owner_id = strip_tags($data['docOwnerId']);
    if (isset($data['file'])) {
        $validator = Validator::make($data, [
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Only PDF files are allowed and size must not exceed 2MB'], 422);
        }


        $fin = FinancialDocuments::join('document_owners', 'document_owners.id', '=', 'financial_document.doc_owner_id')
    ->where('financial_document.id', $doc_id)
    ->where('document_owners.id', $doc_owner_id)
    ->first();


    if ($fin && $fin->doc_path) {
        $fin_path = public_path($fin->doc_path);

        if (file_exists($fin_path)) {
            unlink($fin_path); // Delete the file
        }
$name = $fin->docOwnerFirstName;
$type = $fin->doc_type;

    }
    $file = $data['file'];
    $ext = $file->getClientOriginalExtension();
    $newFileName = time() . '_' . 'updated' . '/'.$name;
    $path = 'uploads/docs/' . $newFileName . "_$type" . '.' . $ext;
    $file->move(public_path('uploads/docs'), $path);

    }



    $updateData = [
        'doc_type' => 'fin',
        'status' => 'submitted',
        'updated_at' => now(),
        'uploaded_by_user_id' => Auth::user()->id,
        'doc_owner_id'=>strip_tags($doc_owner_id),
    ];


    if (isset($data['finName'])) {
        $updateData['bank_name'] = strip_tags($data['finName']);
    }

    if (isset($data['finCountry'])) {
        $updateData['country_code'] = strip_tags($data['finCountry']);
    }

    if (isset($data['finInfo'])) {
        $updateData['description'] = strip_tags($data['finInfo']);
    }


    if (isset($path)) {
        $updateData['doc_path'] = $path;
    }


    $fin->where('id', '=', $doc_id)->update($updateData);

    return true;


}

public function docUpdate(request $request){


    $data = $request->all();
    $validator = Validator::make($request->all(), [
        'docOwnerId' => 'required|string',
        'doc_id' => 'string|required',

    ]);

    $record = [];
    $check = false;

    if(isset($data['firstName']) &&$data['firstName']!=null){
        $record['docOwnerFirstName']=strip_tags( $data['firstName']);
        $check = true;


    }
    if(isset($data['lastName'])&&$data['lastName']!=null){
        $record['docOwnerLastName']= strip_tags($data['lastName']);
        $check = true;


    }
    if(isset($data['middleName'])&&$data['middleName']!=null){
        $record['docOwnerMiddleName']= strip_tags($data['middleName']);
        $check = true;


    }
    if(isset($data['dob']) &&$data['dob']!=null){
        $record['docOwnerDOB']= strip_tags($data['dob']);
        $check = true;

    }
    if($check === true){
        $this->updateDocOwner($record, $data);
    }



    if(isset($data['schoolNameEduc']) && $data['schoolNameEduc']!=null
    ||isset($data['schoolCountryEduc'])&& $data['schoolCountryEduc']!==null
    || isset($data['enrollmentYearEduc'])&& $data['enrollmentYearEduc']!=null){

        $doc_type = 'educ';


    }else if(isset($data['schoolNameProf[']) && $data['schoolNameProf']!=null
    ||isset($data['enrolmentStatusProf'])&& $data['enrolmentStatusProf']!=null
    || isset($data['profCourse'])&& $data['profCourse']!=null){

        $doc_type = 'prof';

    }elseif(isset($data['finName']) && $data['finName']!=null
    ||isset($data['finCountry'])&& $data['finCountry']!=null){

        $doc_type = 'finance';

    }else{


        return response()->json(['success' =>false,'message'=>'Invalid Access'], 401);


    }


    switch ($doc_type) {

        case 'educ':
        $data = $this->updateEducationalDocument($data);
            break;
        case 'prof':
         $data =   $this->updateProfessionalDocument($data);
            break;
        case 'finance':

           $data =  $this->updateFinancialDocument($data);
            break;

    }

    return response()->json(['success' =>true,'message'=>'updated successfully'], 201);



}


}
