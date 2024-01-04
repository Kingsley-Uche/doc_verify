<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\document_owner;
use App\Models\documents;
use App\Models\document_file;
use Illuminate\Support\Facades\Validator;


class DocumentsController extends Controller
{
    //
    public function upload(request $request){

        if($request->docType==='educ'){
            //upload educational documents
          $upload=  $this->upload_educ($request);


        }else{
            //upload others
            $upload = $this->upload_others($request);
        }

        dd($upload);
        //validate documents





        // die();
         $uploadedFiles = [];
if(!empty($request->file)){
    for ($i = 0; $i < count($request['file-name']); $i++) {
        $name = $request['file-name'][$i];



     $file=$request->file('document');
    // dd($file[1]);
     $ext=$file[$i]->getClientOriginalExtension();

        // Use Laravel's file handling

        $destinationPath ='uploads/docs'; // Set your desired storage path
        $newFileName = time() . '_' . $name; // You can customize the new filename as needed
        $path = $destinationPath . '/' . $newFileName.'.'.$ext;


//$file[$i]->move(public_path('uploads/docs'),$path);



        // Save information about the uploaded file
        $uploadedFiles[] = [
            'name' => $name,
            'file' => $newFileName,
            'path'=>$path,
            'size' => $_FILES['document']['size'][$i],
            'type' => $_FILES['document']['type'][$i],

        ];
//implement saving document owner detail
//implement saving on database
//return success message to indicate document has been saved.


}

}else{

}


    echo '<pre>';
    var_dump($uploadedFiles);
    echo '</pre>';
}


private function upload_educ($request){


    $validator = Validator::make($request->all(), [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'dob'=>'required|string|',
        'matricNumber' => 'required|string|',
        'dateOfIssue'=>'required|date',
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
'reference'=>$request->firstName.'/'.substr(md5(uniqid(rand(),true)),0,8),         ]);
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
    }
}
