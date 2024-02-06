<?php
namespace App\Http\Controllers\api;

use App\Models\Institutions;
use App\Models\transactions;
use Illuminate\Http\Request;
use App\Models\ServiceCharge;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



/**
 * The payment processing module handles payment calculation using the uploaded data
 * Since educational documents can have surcharge, it fetches the surcharge of each school.
 *
 */

class PaymentController extends Controller
{
    public function checkout(Request $request)
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
    }



    public function initiatePayment(Request $request)
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

        }



    }

    public function start_transaction(request $request){
        // accepts transaction from the backend
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $this->save_transaction($request);

    }


    private function save_transaction(request $request){
        //handles transaction creation
        $data =$request->all();

$reference = strip_tags( $data['reference']);


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


            return response()->json(['success' => true, 'message' => 'transaction started.'], 200);




    }

    public function confirm_payment(request $request){
        $curl = curl_init();
$reference='hsw1fud4uc';
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER =>  array(
            "Authorization: Bearer " . env('PAYMENTBEARER'),
            "Cache-Control: no-cache",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
         // echo "cURL Error #:" . $err;

         return response()->json(['success' => false, 'message' => 'Network error', 'error'=>$err], 422);

        } else {

            transactions::where('transaction_id', strip_tags($reference))
            ->update([
                'status' => 'confirmed',
                'updated_at'=>now(),
                'description'=>'Payment has been confirmed',

            ]);


            return response()->json(['success' => true, 'message' => 'transaction confirmed', 'data'=>$response], 200);
        }
        //process payment here.


    }
}
