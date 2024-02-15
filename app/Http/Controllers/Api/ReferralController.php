<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\FinancialDocuments;
use App\Http\Controllers\Controller;
use App\Models\EducationalDocuments;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalDocuments;

class ReferralController extends Controller
{
    //


    public function __construct() {
        $user = Auth::user();

        if ($user->is_system_admin || $user->system_admin_type == 'admin_1' || $user->status != 'active') {
            return response()->json(['error' => 'Unauthorized or user not activated'], 401);
        }

//Only an institution that signed up can see the status the document that their potential student is trying to verify

    }
    public function monitor_documents(request $request){
        $user = Auth::user();

        if ($user->company_id) {

            $viewer_code = $user->company_ref;
            $response = [];

            $educationalDocuments = EducationalDocuments::where('viewer_code', $viewer_code)->paginate(50);
            if ($educationalDocuments->total() > 0) {
                $response['educational_documents'] = $educationalDocuments;
            }

            $professionalDocuments = ProfessionalDocuments::where('viewer_code', $viewer_code)->paginate(50);
            if ($professionalDocuments->total() > 0) {
                $response['professional_documents'] = $professionalDocuments;
            }

            $financialDocuments = FinancialDocuments::where('viewer_code', $viewer_code)->paginate(50);
            if ($financialDocuments->total() > 0) {
                $response['financial_documents'] = $financialDocuments;
            }

            // Check if response is empty;
            if (empty($response)) {
                return response()->json(['message' => 'No documents found'], 404);
            }

            return response()->json($response);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


    }
}



