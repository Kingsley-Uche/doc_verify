<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\document_owner;
use App\Models\FinancialDocuments;
use App\Http\Controllers\Controller;
use App\Models\EducationalDocuments;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalDocuments;
use Illuminate\Support\Facades\Validator;

class DocumentsController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' =>'required|string',
            'middleName' =>'string',
            'lastName' => 'required|string',
            'dob' =>'required|date_format:d-m-Y',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $name = $request->firstName . '_' . $request->lastName;








        $response_educ = $this->validateDocuments($request, 'fileDocEduc');
        $response_prof = $this->validateDocuments($request,'fileDocProf');
        $response_fin = $this->validateDocuments($request, 'fileDocFin');

        if (
            (is_array($response_educ) && array_key_exists('errors', $response_educ)) ||
            (is_array($response_prof) && array_key_exists('errors', $response_prof)) ||
            (is_array($response_fin) && array_key_exists('errors', $response_fin))
        ) {
            return response()->json(['errors' => 'Only pdf files allowed', 'success' => false], 422);
        }



        //validate documents
        $errors =[];
        if($request->schoolNameEduc||$request->matricNumber||$request->enrollmentYearEduc||$request->schoolCountryEduc||$request->graduationYearEduc){
            $validator = Validator::make($request->all(), [
                                'schoolNameEduc' => 'required|array',
                                'schoolNameEduc.*' => 'required|string',
                                'matricNumber' => 'required|array',
                                'matricNumber.*' => 'required|string',
                                'dateOfIssueEduc' => 'required|array',
                                'dateOfIssueEduc.*' => 'required|date_format:d-m-Y',
                                'examBoard' => 'required|array',
                                'examBoard.*' => 'required|string',
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
                               // return response()->json(['errors' => $validator->errors()], 422);
                               $err['errors']=$validator->errors()->toArray();
                               $errors[] =$err;
                            }







        }

        if($request->schoolNameProf||$request->studentIdProf||$request->enrollmentYearProf||$request->schoolCountryProf||$request->graduationYearProf){



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
                                'profCourse' => 'array',
                                'profCourse.*' => 'required|string',
                                'enrolmentStatus' => 'array',
                                'enrolmentStatus.*' => 'required|string',
                                'schoolCountryProf' => 'required|array',
                                'schoolCountryProf.*' => 'required|string',
                            ]);

                            if ($validator->fails()) {
                                $err=$validator->errors()->toArray();
                                $errors[] =$err;

                            }




        }


        if($request->bank_name){




        }
       // dd($errors);
       $containsError = collect($errors)->contains(function ($error) {
        return !empty($error['errors']);
    });

    // Convert to JSON response
    if ($containsError) {
        return response()->json(['errors' => $errors, 'success' => false], 422);
    }


$educationData = [];
$data =$request->all();

//for education file uploads
$fileKey ='fileDocEduc';
$type ='educ';
if ($request->has($fileKey)) {
    $path_array = [];
    $index = 0;
    $response = [];

    foreach ($request->$fileKey as $key => $value) {
        $index++;
        $file = $request->file($fileKey)[$key];
        $ext = $file->getClientOriginalExtension();
        $destinationPath = 'uploads/docs';
        $newFileName = time() . '_' . $index . '_' . $name;
        $path = $destinationPath . '/' . $newFileName . "_$type" . '.' . $ext;
        $file->move(public_path('uploads/docs'), $path);
        $path_array[] = $path;
    }



}


$fileKey ='fileDocProf';
$type ='prof';
if ($request->has($fileKey)) {
    $path_prof = [];
    $index = 0;
    $response = [];

    foreach ($request->$fileKey as $key => $value) {
        $index++;
        $file = $request->file($fileKey)[$key];
        $ext = $file->getClientOriginalExtension();
        $destinationPath = 'uploads/docs';
        $newFileName = time() . '_' . $index . '_' . $name;
        $path = $destinationPath . '/' . $newFileName . "_$type" . '.' . $ext;
        $file->move(public_path('uploads/docs'), $path);
        $path_prof[] = $path;
    }


}
if(count($data['matricNumber'])!=count($data['dateOfIssueEduc'])||count($data['schoolNameEduc'])!= count($data['courseOrSubject'])){
    return response()->json(['errors' =>'Incomplete fields', 'success' => false], 422);

}

$doc_owner_id = $this->saveDocumentOwner($request, $name);

foreach ($data['schoolNameEduc'] as $key => $value) {


    $this->save_educational($request, $key, $value, $doc_owner_id, $path);

}
foreach ($data['schoolNameProf'] as $key => $value) {


    $this->save_professional($request, $key, $value, $doc_owner_id, $path);

}






        return response()->json(['message' => 'Upload successful', 'success' => true], 201);
    }

    private function saveDocumentOwner(Request $request, $name)
    {

        $document_owner = document_owner::create([
            'docOwnerFirstName' => strip_tags($request->firstName),
            'docOwnerMiddleName' => strip_tags($request->middleName),
            'docOwnerLastName' => strip_tags($request->lastName),
            'docOwnerDOB' => strip_tags($request->dob),
            'uploaded_by_user_id'=>Auth::user()->id,
        ]);

        return $document_owner->id;
    }

    private function validateDocuments(Request $request,  $fileKey)
    {
        if ($request->has($fileKey)) {
            $response = [];
            $validator = Validator::make($request->all(), [
                $fileKey => "required|array",
                $fileKey.'.*' => "required|mimes:pdf|max:2048",

            ]);

            if ($validator->fails()) {
                $response['errors'] = $validator->errors()->toArray();
                return $response;
            }


        }

        return true;
    }



    private function save_educational(Request $request, $key, $value, $doc_owner_id, $path)

    {
       $data = $request->all();
       EducationalDocuments::create([
        'course'=>strip_tags($data['courseOrSubject'][$key]),
        'doc_verifier_country' =>strip_tags( $data['schoolCountryEduc'][$key]),
        'document_category' => 'educational',
        'country_code'=>strip_tags($data['schoolCountryEduc'][$key]),
        'doc_owner_id'=>strip_tags($doc_owner_id),
        'studentId' => strip_tags($data['matricNumber'][$key]),
        'date_of_issue' => $data['dateOfIssueEduc'][$key],
        'exam_board' =>strip_tags($data['examBoard'][$key]),
        'verifier_name' => strip_tags($value),
        'verifier_id'=>null,
        'viewer_code'=>null,
        'verifier_city' => strip_tag($data['schoolCity'][$key]),
        'status'=>'submitted',
        'ref_id'=> strip_tags($request->firstName).'/'.substr(md5(uniqid(rand(),true)),0,8),
        'start_year' =>strip_tags($data['enrollmentYearEduc'][$key]),
        'end_year' => strip_tags($data['graduationYearEduc'][$key]),
        'doc_info' => strip_tags($data['addInfo'][$key]),
        'course' =>strip_tags($data['courseOrSubject'][$key]),
        'doc_path'=>$path,
        'created_at' => now(),
        'updated_at' => now(),

    ],);
    }

    private function save_professional(Request $request, $key, $value, $doc_owner_id, $path){
        $data = $request->all();

        ProfessionalDocuments::create([
            'document_category' => 'professional',
            'country_code'=>strip_tags($data['schoolCountryProf'][$key]),
            'doc_owner_id'=>$doc_owner_id,
            'studentId' =>strip_tags($data['studentIdProf'][$key]),
            'doc_verifier_name' => $value,
            'doc_verifier_id'=>null,
            'viewer_code'=>null,
            'enrollment_status' =>strip_tags($data['enrolmentStatusProf'][$key]),
            'qualification'=>strip_tags($data['qualificationProf'][$key]),
            'status'=>'submitted',
            'ref_id'=> strip_tags($request->firstName).'/'.substr(md5(uniqid(rand(),true)),0,8),
            'start_year' => strip_tags($data['enrollmentYearProf'][$key]),
            'end_year' => strip_tags($data['graduationYearProf'][$key]),
            'add_info' => strip_tags($data['addInfoProf'][$key]),
            'course' => strip_tags($data['profCourse'][$key]),
            'doc_path'=>$path,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }

}
