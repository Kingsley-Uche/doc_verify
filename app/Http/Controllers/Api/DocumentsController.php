<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\document_owner;
use App\Models\documents;
use App\Models\document_file;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Validator;


class DocumentsController extends Controller
{
    //
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'middleName' => 'string',
            'lastName' => 'required|string',
            'dob' => 'required|date_format:d-m-Y',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $educ = false;
        $prof = false;
        $fin = false;
        $name = $request->firstName.'_'.$request->lastName;
        for ($i = 0; $i < count($request->docType); $i++) {
            if ($request->docType[$i] === 'educ') {
                $educ = true;
                $number_edu_docs = count($request->schoolNameEduc);
            }
            if ($request->docType[$i] == 'prof') {
                $prof = true;
                $number_prof_docs = count($request->schoolNameProf);
            }

        }

        if ($educ === true) {
            $validator = Validator::make($request->all(), [
                'schoolNameEduc' => 'required|array',
                'schoolNameEduc.*' => 'required|string',
                'matricNumber' => 'required|array',
                'matricNumber>.*' => 'required|string',
                'dateOfIssue.*' => 'required|array',
                'dateOfIssue.*' => 'required|date_format:d-m-Y',
                'examBoard' => 'required|array',
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
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Handle upload for educational documents
            // Implement saving document owner details
            // Implement saving to the database
        }

        if ($prof === true) {
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
                'courseOrSubject' => 'array',
                'courseOrSubject.*' => 'required|string',
                'enrolmentStatus' => 'array',
                'enrolmentStatus.*' => 'required|string',
                'schoolCountryProf' => 'required|array',
                'schoolCountryProf.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Handle upload for professional documents
            // Implement saving document owner details
            // Implement saving to the database
        }
$reference = $request->firstName.'/'.substr(md5(uniqid(rand(),true)),0,8);

        // Other processing logic...
        // save doc owner's information

       $document_owner = document_owner::create([
            'docOwnerFirstName'=>strip_tags($request->firstName),
            'docOwnerMiddleName'=>strip_tags($request->middleName),
            'docOwnerLastName'=>strip_tags($request->lastName),
            'docOwnerDOB'=>strip_tags($request->dob),
            'reference'=>$reference, ]);
    $doc_owner_id = document_owner::latest()->first()->id;

    if(!empty($request->files)){
        $docs = $request->files;

        // if($docs->fileDocEduc){
        //     echo 'available';
        $validator = Validator::make($request->all(), [
            'fileDocEduc.*' => 'required|file|mimes:pdf|max:2048', // Adjust the validation rules as needed
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors(), 'success'=>false], 422);
        }

        //check for educational documents and upload


        if(!empty($request->file('fileDocEduc'))){
            $count =count($request->file('fileDocEduc'));

            //upload docs
        for($i=0;$i<$count;$i++){


            $file = $request->file('fileDocEduc')[$i];


            $ext = $file->getClientOriginalExtension();
            $destinationPath = 'uploads/docs';
            $newFileName = time() . '_' . $name;
            $path = $destinationPath . '/' . $newFileName.'_educ_'. '.' . $ext;

            // Move the file to the destination path
            $file->move(public_path('uploads/docs'), $path);


          //  $file = $request->file('fileDocProf')[0]


        }



        }

 //Check for professional documents and upload

 $validator = Validator::make($request->all(), [
    'fileDocProf.*' => 'required|file|mimes:pdf|max:2048', // Adjust the validation rules as needed
]);
if($validator->fails()){
    return response()->json(['errors' => $validator->errors()], 422);
}

if(!empty($request->file('fileDocProf'))){
    $count =count($request->file('fileDocProf'));

    //upload docs
for($i=0;$i<$count;$i++){


    $file = $request->file('fileDocProf')[$i];


    $ext = $file->getClientOriginalExtension();
    $destinationPath = 'uploads/docs';
    $newFileName = time() . '_' . $name;
    $path = $destinationPath . '/' . $newFileName.'_prof'.$ext;

    // Move the file to the destination path
    //$file->move(public_path('uploads/docs'), $path);
    //
  //  $file = $request->file('fileDocProf')[0]


}



}


//upload the documents information for education

$educationData = [];
$data =$request->all();

foreach ($data['schoolCountryEduc'] as $key => $value) {
    $educationData[] = [
        'document_verifier_country' => $value,
        'document_category' => $data['docType'][$key],
        'document_owner_id'=>$doc_owner_id,
        'matric_number' => $data['matricNumber'][$key],
        'date_of_issue_educ' => $data['dateOfIssueEduc'][$key],
        'exam_board' => $data['examBoard'][$key],
        'document_verifier_name' => $data['schoolNameEduc'][$key],
        'document_verifier_city' => $data['schoolCity'][$key],
        'document_status'=>'submitted',
        'document_ref_code'=> $request->firstName.'/'.substr(md5(uniqid(rand(),true)),0,8),
        'doc_start_year' => $data['enrollmentYearEduc'][$key],
        'doc_end_year' => $data['graduationYearEduc'][$key],
        'doc_info' => $data['addInfo'][$key],
        'doc_course' => $data['courseOrSubject'][$key],

    ];
}
$docs = document::create($educationData);
//Education::insert($educationData);







    }else{

      //files were not selected
        return response()->json(['errors' =>'Select at least one file', 'success'=>false], 422);

    }


        // Other processing logic...

        return response()->json(['message' => 'Upload successful', 'success'=>true], 201);
    }



private function upload_educ($request){


    $validator = Validator::make($request->all(), [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'dob'=>'required|string|',
        'matricNumber' => 'required|string|',
        'dateOfIssue'=>'required|array',
        'schoolName'=>'required|string',
        'schoolCity'=>'required|string',
        'schoolCountry'=>'required|numeric|min:1',
        'enrollmentYear'=>'required|string|',
        'graduationYear'=>'required|string',
        'addInfo'=>'required|string',
        'course'=>'required|string',
        'docToVerify'=>'required|string',
    ]);


    if ($validator->fails()) {

        return  response()->json(['errors'=>$validator->errors()],422);
         //give feedback
     }else{


         $validatedData = $validator->validated();
     $doc_owner=    document_owner::create([
'docOwnerFirstName'=>strtolower(strip_tags($validatedData['firstName'])),
'docOwnerMiddleName'=>strtolower(strip_tags($validatedData['middleName'])),
'docOwnerLastName'=>strtolower(strip_tags($validatedData['lastname'])),
'docOwnerDOB'=>strip_tags($validatedData['dob']),
'reference'=>$request->firstName.'/'.substr(md5(uniqid(rand(),true)),0,8), ]);
$docOwnerId = document_owner::latest()->first()->id;

//save the document
$document = document::create([
    'document_owner_id'=>$docOwnerId,
    'document_verifier_name'=>strip_tags($validatedData['schoolName']),
    'document_verifier_country'=>strip_tags($validatedData['schoolCountry']),
    'doc_start_year'=>strip_tags($validatedData['enrollmentYear']),
    'doc_end_year'=>strip_tags($validatedData['graduation']),
    'document_status'=>'submitted',
    'document_ref_code'=>$doc_owner['reference'],
    'doc_matric_number'=>strip_tags($validatedData['matricNumber']),
    'doc_info'=>strip_tags($validatedData['addInfo']),
]);
//save supporting files
$uploadedFiles = [];
if (!empty($request->file)) {
    $uploadedFiles = [];

    for ($i = 0; $i < count($request['file-name']); $i++) {
        $name = $request['file-name'][$i];
        $file = $request->file('document');
        $ext = $file[$i]->getClientOriginalExtension();

        // Validate file type
        if ($ext !== 'pdf') {
            // If the file is not a PDF, return a JSON response
            return response()->json(['error' => 'Only PDF files are allowed.'], 400);
        }

        // Use Laravel's file handling
        $destinationPath = 'uploads/docs'; // Set your desired storage path
        $newFileName = time() . '_' . $name; // You can customize the new filename as needed
        $path = $destinationPath . '/' . $newFileName . '.' . $ext;

        // Move the file to the destination path
        $file[$i]->move(public_path('uploads/docs'), $path);

        // Save information about the uploaded file
        $uploadedFiles[] = [
            'name' => $name,
            'file' => $newFileName,
            'path' => $path,
            'size' => $_FILES['document']['size'][$i],
            'type' => $_FILES['document']['type'][$i],
        ];
    }

    // Process the uploaded files as needed

    // Return a success response if needed
    return response()->json(['message' => 'Files uploaded successfully.'], 200);
}

}
     }

     private function runner(){
        echo 'we are here';
     }
}
