<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\document_owner;
use App\Models\FinancialDocuments;
use App\Http\Controllers\Controller;
use App\Models\EducationalDocuments;
use App\Models\ProfessionalDocuments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DocumentsController extends Controller
{


    public function __construct() {
        $user = Auth::user();
        if ($user !== null && ($user->is_system_admin || $user->system_admin_type == 'admin_1')) {
            abort(401, 'Unauthorized for Administrators');
        }

        if ($user == null) {
            abort(401, 'Unauthorized Access');
        }
    }


    public function upload(Request $request)
    {


        //implement filetype educ and filetype financial

        //implement referel link for companies
        //Therewill be a table that contains the company's id alongside its referal link
        //When documents are submiited through the company, the referal table is accessed and the company's id is saved as the foreign key of the document viewer_id
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'middleName' => 'string',
            'lastName' => 'required|string',
            'dob' => 'required|date_format:d-m-Y',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $deta = $request->all();

        $name = strip_tags($deta['firstName']) . '_' . $deta['lastName'];

        $responseEduc = $this->validateDocuments($request, 'fileDocEduc');
        $responseProf = $this->validateDocuments($request, 'fileDocProf');
        $responseFin = $this->validateDocuments($request, 'fileDocFin');

        $errors = [];

        foreach (['responseEduc', 'responseProf', 'responseFin'] as $responseKey) {
            if (is_array($$responseKey) && array_key_exists('errors', $$responseKey) && !empty($$responseKey['errors'])) {
                $errors = array_merge($errors, $$responseKey['errors']);
            }



        }


        if (!empty($errors)) {
            return response()->json(['errors' => true, 'message' => $errors, 'success' => false], 422);
        }

        $errors = array_merge($errors,$this->validateEducationalData($request));
        $errors =array_merge($errors, $this->validateProfessionalData($request));
        $errors =array_merge($errors, $this->validateFinancialData($request));


        if (!empty($errors)) {
            return response()->json(['errors' => $errors, 'success' => false], 422);
        }

        $docOwnerId = $this->saveDocumentOwner($request, $name);


        $this->saveDocuments($request, 'fileDocEduc', $name, $docOwnerId, 'educ');
        $this->saveDocuments($request, 'fileDocProf', $name, $docOwnerId, 'prof');
        $this->saveDocuments($request, 'fileDocFin', $name, $docOwnerId, 'finance');

        return response()->json(['message' => 'Upload successful', 'success' => true], 201);
    }

    private function validateDocuments(Request $request, $fileKey)
    {
        if ($request->has($fileKey)) {
            $validator = Validator::make($request->all(), [
                $fileKey => 'required|array',
                $fileKey . '.*' => 'required|mimes:pdf|max:2048',
            ]);

            if ($validator->fails()) {
                return ['errors' => $validator->errors()->toArray()];
            }
        }

        return [];
    }

    private function validateEducationalData(request $request)
    {
        $data =$request->all();
        $errors = [];

        if (isset($data['schoolNameEduc'])) {


            $validator = Validator::make($request->all(), [
                'schoolNameEduc' => 'required|array',
                'schoolNameEduc.*' => 'required|string',
                'matricNumber' => 'required|array',
                'matricNumber.*' => 'required|string',
                'examBoard' => '|array',
                'examBoard.*' => '|string',
                'schoolCity' => 'required|array',
                'schoolCity.*' => 'required|string',
                'enrollmentYearEduc' => 'required|array',
                'enrollmentYearEduc.*' => 'required|date_format:Y',
                'graduationYearEduc' => 'required|array',
                'graduationYearEduc.*' => 'required|date_format:Y',
                'addInfo' => 'array',
                'addInfo.*' => 'string',
                'courseOrSubject' => 'required|array',
                'courseOrSubject.*' => 'required|string',
                'schoolCountryEduc' => 'required|array',
                'schoolCountryEduc.*' => 'required|string',
            ]);
            if ($validator->fails()) {
                $errors['educationalData'] = $validator->errors()->toArray();
            }
        }
unset ($validator);
        return $errors;
    }


    private function validateProfessionalData(request $request)
    {$data =$request->all();

        $errors = [];


        if(isset($data['schoolNameProf'])||isset($data['studentIdProf'])){



            $validator = Validator::make($request->all(), [
                'schoolNameProf' => 'required|array',
                'schoolNameProf.*' => 'required|string',
                'studentIdProf' => 'required|array',
                'studentIdProf.*' => 'required|string',
                'qualificationProf' => 'required|array',
                'qualificationProf.*' => 'required|string',
                'enrollmentYearProf' => 'required|array',
                'enrollmentYearProf.*' => 'required|date_format:Y',
                'graduationYearProf' => 'required|array',
                'graduationYearProf.*' => 'required|string',
                'addInfo' => 'array',
                'addInfo.*' => 'string',
                'profCourse' => 'array|required',
                'profCourse.*' => 'required|string',
                'enrolmentStatus' => 'array',
                'enrolmentStatus.*' => 'required|string',
                'schoolCountryProf' => 'required|array',
                'schoolCountryProf.*' => 'required|string',
            ],);

            if ($validator->fails()) {
                $errors['professionallData'] = $validator->errors()->toArray();
            }

        }
        unset ($validator);

        return $errors;
    }


    private function validateFinancialData(request $request)
    {

        $data =$request->all();
        $errors = [];

        if (isset($data['finName'])) {


            $validator = Validator::make($request->all(), [
                'finName' => 'required|array',
                'finName.*' => 'required|string',
                'finCountry' => 'required|array',
                'finCountry.*' => 'required|string',]);

            if ($validator->fails()) {
                $errors['financialData'] = $validator->errors()->toArray();

            }
            unset ($validator);

        }


        return $errors;

    }





    private function saveDocumentOwner(Request $request, $name)
    {
        $documentOwner = document_owner::create([
            'docOwnerFirstName' => strip_tags($request->firstName),
            'docOwnerMiddleName' => strip_tags($request->middleName),
            'docOwnerLastName' => strip_tags($request->lastName),
            'docOwnerDOB' => strip_tags($request->dob),
            'uploaded_by_user_id' => Auth::user()->id,
        ]);

        return $documentOwner->id;
    }

    private function saveDocuments(Request $request, $fileKey, $name, $docOwnerId, $type)
    {



        if ($request->files->has($fileKey)) {

            $pathArray = [];
            $index = 0;

            foreach ($request->$fileKey as $key => $value) {

                $index++;
                $file = $request->file($fileKey)[$key];
                $ext = $file->getClientOriginalExtension();
                $newFileName = time() . '_' . $index . '_' . $name;
                $path = 'uploads/docs/' . $newFileName . "_$type" . '.' . $ext;
                $file->move(public_path('uploads/docs'), $path);
                $pathArray[] = $path;


                // Save documents to the respective tables based on $type
            $this->saveDocument($type, $request, $key, $value, $docOwnerId, $path);
            }

        }

    }

    private function saveDocument($type, Request $request, $key, $value, $docOwnerId, $path,)
    {
        $referer_inst = $request->input('referer', 'default');
        switch ($type) {
            case 'educ':
                $this->saveEducationalDocument($request, $key, $value, $docOwnerId, $path, $referer_inst);
                break;
            case 'prof':
                $this->saveProfessionalDocument($request, $key, $value, $docOwnerId, $path, $referer_inst);
                break;
            case 'finance':

                $this->saveFinancialDocument($request, $key, $value, $docOwnerId, $path,$referer_inst);
                break;
            // Add more cases as needed...
        }
    }

    private function saveEducationalDocument(Request $request, $key, $value, $docOwnerId, $path,$referer_inst)
    {
        // Save educational documents
        if($request->schoolNameEduc){
            $data = $request->all();
            $referer_inst = $request->input('type', 'default');

        EducationalDocuments::create([
             'course'=>strip_tags($data['courseOrSubject'][$key]),
             'doc_verifier_country' =>strip_tags( $data['schoolCountryEduc'][$key]),
             'document_category' => 'educational',
             'country_code'=>strip_tags($data['schoolCountryEduc'][$key]),
             'doc_owner_id'=>strip_tags($docOwnerId),
             'studentId' => strip_tags($data['matricNumber'][$key]),
             'exam_board' => isset($data['examBoard'][$key]) ? strip_tags($data['examBoard'][$key]) : null,
             'verifier_name' => strip_tags($data['schoolNameEduc'][$key]),
             'verifier_id'=>null,
             'viewer_code'=>strip_tags($referer_inst),
             'verifier_city' => strip_tags($data['schoolCity'][$key]),
             'status'=>'submitted',
             'ref_id'=> strip_tags($request->firstName).'/'.substr(md5(uniqid(rand(),true)),0,8),
             'start_year' =>strip_tags($data['enrollmentYearEduc'][$key]),
             'end_year' => strip_tags($data['graduationYearEduc'][$key]),
             'doc_info' => isset($data['addInfo'][$key]) ? strip_tags($data['addInfo'][$key]) : null,
             'course' =>strip_tags($data['courseOrSubject'][$key]),
             'doc_path'=>$path,
             'created_at' => now(),
             'updated_at' => now(),
             'uploaded_by_user_id'=>Auth::user()->id,

         ],);

        }
    }

    private function saveProfessionalDocument(Request $request, $key, $value, $docOwnerId, $path, $referer_inst)
    {
        $data =$request->all();

        if($request['schoolNameProf']){

            // Save professional documents
            ProfessionalDocuments::create([
                'document_category' => 'professional',
                'country_code'=>strip_tags($data['schoolCountryProf'][$key]),
                'doc_owner_id'=>$docOwnerId,
                'studentId' =>strip_tags($data['studentIdProf'][$key]),
                'doc_verifier_name' =>strip_tags($data['schoolNameProf'][$key]),
                'doc_verifier_id'=>null,
                'viewer_code'=>strip_tags($referer_inst),
                'enrollment_status' =>strip_tags($data['enrolmentStatusProf'][$key]),
                'qualification'=>strip_tags($data['qualificationProf'][$key]),
                'status'=>'submitted',
                'ref_id'=> strip_tags($data['firstName']).'/'.substr(md5(uniqid(rand(),true)),0,8),
                'start_year' => strip_tags($data['enrollmentYearProf'][$key]),
                'end_year' => strip_tags($data['graduationYearProf'][$key]),
                'add_info' => isset($data['addInfoProf'][$key]) ? strip_tags($data['addInfoProf'][$key]) : null,
                'course' => strip_tags($data['profCourse'][$key]),
                'doc_path'=>$path,
                'created_at' => now(),
                'updated_at' => now(),
                'uploaded_by_user_id'=>Auth::user()->id,
            ]);

        }

    }

    private function saveFinancialDocument(Request $request, $key, $value, $docOwnerId, $path,$referer_inst)
    {
        $data = $request->all();

if($data['finName']){
    $referer_inst = $request->input('type', 'default');



    FinancialDocuments::create([
        'doc_owner_id'=>$docOwnerId,
        'bank_name'=>strip_tags($data['finName'][$key]),
        'country_code'=>strip_tags($data['finCountry'][$key]),
        'description'=>isset($data['finInfo'][$key]) ? strip_tags($data['finInfo'][$key]) : null,
        'doc_path'=>$path,
        'ref_id'=>strip_tags($data['firstName']).'/'.substr(md5(uniqid(rand(),true)),0,8),
        'status'=>'submitted',
        'created_at' => now(),
        'updated_at' => now(),
        'viewer_code'=>strip_tags($referer_inst),
        'uploaded_by_user_id'=>Auth::user()->id,
    ]);

}


    }




public function view_documents(Request $request)
{
    $user_id = auth()->user()->id;
    $response = [];

    if ($request->type) {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $this->get_all_documents($user_id, $request->type);
    } else {
        $response['data'] = $this->get_all_documents($user_id);
    }

    return response()->json(['data' => $response, 'success' => true], 200);
}




 private function get_all_documents($id, $type = null)
{
    $user2verify = document_owner::where('uploaded_by_user_id', '=', $id)->get();
    $response = [];


    foreach ($user2verify as $user => $value) {
    $documentSet = [
        'educationalDocuments' => $this->getEducationalDocuments($value->id, $type, $value,  $doc_id=null),
        'professionalDocuments' => $this->getProfessionalDocuments($value->id, $type, $value,  $doc_id=null),
        'financialDocuments' => $this->getFinancialDocuments($value->id, $type, $value, $doc_id =null),
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

}


    private function getEducationalDocuments($docOwnerId, $type =null, $value, $doc_id){
        $user_logged_in_id = Auth::user()->id;


$query = EducationalDocuments::where('doc_owner_id', $docOwnerId)
    ->where(function ($query) use ($type, $user_logged_in_id,$doc_id) {
          if (isset($type)&&$type!= null) {

            $query->where('status', $type);
        }

        if(isset($docOwnerId)&&$docOwnerId!=null ){


            $query->where('doc_owner_id', $docOwnerId);



        }
        if(isset($doc_id)&&$doc_id!=null){
            $query->where('id','=',$doc_id);
        }
            $query->where('uploaded_by_user_id', $user_logged_in_id);




    });

$educational_files = $query->get();


return $educational_files;



    }




    private function getProfessionalDocuments($docOwnerId, $type=null, $value, $doc_id){

        $user_logged_in_id = Auth::user()->id;

        $query = ProfessionalDocuments::where('doc_owner_id', $docOwnerId)
            ->where(function ($query) use ($type, $user_logged_in_id) {
                if (isset($type)||$type!= null) {
                    $query->where('status', $type);
                }

                if(isset($doc_owner_id)&&$doc_owner_id!=null ){
                    $query->Where('doc_owner_id', $doc_owner_id);



                }

                if(isset($doc_id)&&$doc_id!=null){
                    $query->Where('id','=',$doc_id);
                }

                  $query->Where('uploaded_by_user_id', $user_logged_in_id);




            });

            $professional_files = $query->get();


            return $professional_files;


    }


    private function getFinancialDocuments($docOwnerId, $type =null, $value, $doc_id){

        $user_logged_in_id = Auth::user()->id;


        $query = FinancialDocuments::where('doc_owner_id', $docOwnerId)
        ->where(function ($query) use ($type, $user_logged_in_id) {
            if (isset($type)||$type!= null) {
                $query->where('status', $type);
            }

            if(isset($doc_owner_id)&&$doc_owner_id!=null ){
                $query->Where('doc_owner_id', $doc_owner_id);



            }
            if(isset($doc_id)&& $doc_id!=null){
                $query->Where('id','=',$doc_id);
            }
               $query->Where('uploaded_by_user_id', $user_logged_in_id);




        });

        $financial_files = $query->get();


        return $financial_files;
;


    }


    public function get_by_doc_owner_id(request $request){
$type =null;

        $validator = Validator::make($request->all(), [
            'docOwnerId' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doc_owner_id = strip_tags($request->docOwnerId);




        $documentSet['educationalDocuments']= $this->getEducationalDocuments($doc_owner_id, $type, $value=null, $doc_id= null,);
        $documentSet['professionalDocuments']=$this->getProfessionalDocuments($doc_owner_id, $type, $value = null, $doc_id = null);
    $documentSet['financialDocuments'] =$this->getFinancialDocuments($doc_owner_id, $type, $value= null, $doc_id=null);

 $info['user']['documents']=$documentSet;
 $info['user']['info']=document_owner::where('id', '=', $doc_owner_id)->get();


 $response[] = $info;
 unset($info);

return $response;

    }


    public function get_document_by_id(request $request){


        $validator = Validator::make($request->all(), [
            'docOwnerId' => 'required|string',
            'doc_category'=>'required|string',
            'doc_id'=>'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $doc_owner_id = strip_tags($data['docOwnerId']);
$cat = strip_tags($data['doc_category']);
$doc_id = strip_tags($data['doc_id']);
$data =[];


        switch ($cat) {
            case 'educ';

              $data['document']=  $this->getEducationalDocuments($doc_owner_id, $type=null,$value=null, $doc_id,);

                break;
            case 'prof';
            $data['document'] = $this->getProfessionalDocuments($doc_owner_id,$type= null, $value= null,$doc_id, );
                break;
            case 'finance';

            $data['document']= $this->getFinancialDocuments($doc_owner_id,$type =null, $value= null,$doc_id,);
                break;
            // Add more cases as needed...
        }
 $data['owner']= document_owner::where('id', '=', $doc_owner_id)->first();
        return response()->json(['data' => $data, 'success' => true], 200);

    }

}



