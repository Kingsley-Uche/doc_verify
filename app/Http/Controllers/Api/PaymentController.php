<?php
namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Institutions;
use App\Models\ServiceCharge;
use Illuminate\Support\Facades\Auth;
use App\Models\transactions;



/**
 * The payment processing module handles payment calculation using the uploaded data
 * Since educational documents can have surcharge, it fetches the surcharge of each school.
 *
 */

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $total = 0;
         //Educational
        if (isset($request->schoolNameEduc)) {
            $info['educational'] = [
                'number_docs' => count($request->schoolNameEduc),
                'documents' => [],
            ];

            foreach ($request->schoolNameEduc as $school) {
                $educServiceCharge = $this->getEducationalCharge($user->category_id);
                $surcharge = $this->getSurcharge($school);

                $documentTotal = $educServiceCharge + $surcharge;

                $info['educational']['documents'][] = [
                    'school' => $school,
                    'charge_per_one' => $educServiceCharge,
                    'surcharge' => $surcharge,
                    'charge_total' => $documentTotal,
                ];

                $total += $documentTotal;
            }
        }

        // Professional
        if (isset($request->schoolNameProf)) {
            $professionalServiceCharge = $this->getProfessionalCharge($user->category_id);
            $info['professional'] = [
                'number_docs' => count($request->schoolNameProf),
                'charge_per_one' => $professionalServiceCharge,
                'charge_total' => $professionalServiceCharge * count($request->schoolNameProf),
            ];

            $total += $info['professional']['charge_total'];
        }

        // Financial
        if (isset($request->finName)) {
            $financialServiceCharge = $this->getFinancialCharge($user->category_id);
            $info['financial'] = [
                'number_docs' => count($request->finName),
                'charge_per_one' => $financialServiceCharge,
                'charge_total' => $financialServiceCharge * count($request->finName),
            ];

            $total += $info['financial']['charge_total'];
        }

        $paymentDetails = [
            'total_amount' => $total,
            'email' => $user->email,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'data'=>$info,
        ];

        return response()->json(['success' => true, 'data' => $paymentDetails], 201);
    }

    // ... (other methods)

    private function getEducationalCharge($userCategory)
    {
        return ServiceCharge::where('category_user_id', $userCategory)
            ->where('doc_cat', 'educational')
            ->value('doc_charge') ?? 0;
    }

    private function getSurcharge($school)
    {
        $institutionSurcharge = Institutions::join('surcharge_models', 'surcharge_models.institution_id', '=', 'institutions.id')
            ->where('institutions.name', $school)
            ->value('institution_charge');

        return $institutionSurcharge ?? 0;
    }

    private function getProfessionalCharge($userCategory)
    {
        return ServiceCharge::where('category_user_id', $userCategory)
            ->where('doc_cat', 'professional')
            ->value('doc_charge') ?? 0;
    }

    private function getFinancialCharge($userCategory)
    {
        return ServiceCharge::where('category_user_id', $userCategory)
            ->where('doc_cat', 'financial')
            ->value('doc_charge') ?? 0;
    }public function initiatePayment(Request $request)
    {
        $data = $request->all();

        $url = "https://api.paystack.co/transaction/initialize";

        $fields = [
            'email' => Auth::user()->email,
            'amount' => $data['amount'] * 100,
        ];

        $fields_string = http_build_query($fields);

        // open connection
        $ch = curl_init();

        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYMENTBEARER'),
            "Cache-Control: no-cache",
        ));

        // So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // execute post
        $result = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return response()->json(['success' => false, 'error' => curl_error($ch)], 500);
        }

        // close connection
        curl_close($ch);
        $data = json_decode($result);

        //check for data and update on the database
        if ($data && $data->status) {
            $authorizationUrl = $data->data->authorization_url;
            $accessCode = $data->data->access_code;
            $reference = $data->data->reference;
            //call create transaction

            transactions::create([
                'doc_id'=>000,
                'amount'=>strip_tags($request->amount),
                'description'=>'Not confirmed',
                'transaction_id'=>$reference,
                'status'=>'initiated',
                'transaction_user_id'=>Auth::user()->id,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);


            return response()->json(['success' => true, 'data' => $result], 200);

        } else {
            return response()->json(['success' => false, 'message' => 'failed'], 401);
            // Handle the case where decoding fails or status is false
            echo "Failed to decode JSON or status is false\n";
        }
        die();


    }


}
