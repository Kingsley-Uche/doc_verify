<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\institutions;
use App\Models\serviceCharge;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    //
    public function checkout(request $request){

$data = $request->all();
$info =[];
$user =Auth::user();
$email = $user->email;
$user_category = $user->category_id;
$total =[];
if(isset($data['schoolNameEduc'])){
    $surcharge_all =[];

    $number_educ = count($data['schoolNameEduc']);
    $educ_service_charge = $this->get_educational_charge($user_category);
    //$total_educ_servic_charge['total'] = floatval($educ_service_charge*$number_educ);
    $info['educational']['number_docs'] = $number_educ;
    $info['educational']['charge_per_one'] = $educ_service_charge;
    $info['educational']['charge_total']= floatval($educ_service_charge*$number_educ);



    foreach($data['schoolNameEduc'] as $school){

        $surcharge['surcharge'] = $this->get_surcharge($school);
        $surcharge['inst']= $school;

        $surcharge_all= $surcharge;
        $total['total']= floatval($info['educational']['charge_total'] +$surcharge['surcharge']);


    }
     array_push($total,$surcharge_all, $info);




}
unset($info);

if(isset($data['schoolNameProf'])){


    $professional_service_cost=$this -> get_professional_charge($user_category);
    $professional_number= count($data['schoolNameProf']);



    if($professional_service_cost->isNotEmpty()){
       $charge = $professional_service_cost->first()->doc_charge;
    }else{
        $charge =0;

    }


    $cost_professional  = floatval($charge)*floatval($professional_number);

    $info['professional']['number_docs'] = $professional_number;
    $info['professional']['charge_per_one'] = $charge;
    $info['professional']['charge_total']= $cost_professional;
    if(isset($total['total'])){

        $total['total'] =   floatval($total['total']+$cost_professional);
    }
    array_push($total,$info);

    unset($charge);



    //   $cost = $this->calculate_cost($request);
    //   return response()->json(['success' => true, 'cost'=>$cost], 422);
    }


    unset($info);

    if (isset($request['finName'])) {
        $financial_number = count($request['finName']);
        $financial_service_cost =$this->get_financial_charge($user_category);



        if ($financial_service_cost->isNotEmpty()) {

            $charge = $financial_service_cost->first()->doc_charge;
        } else {
            // The collection is empty, set charge to 0
            $charge = 0;

        }






        $cost_financial = floatval($charge) * floatval($financial_number);
        $info['financial']['number_docs'] = $financial_number;
        $info['financial']['charge_per_one'] = $charge;
        $info['financial']['charge_total']= $cost_financial;
        if(isset($total['total'])){

            $total['total'] =   floatval($total['total']+$cost_financial);
        }
        array_push($total,$info);

        unset($charge);




        }
        unset($info);

          return response()->json(['success' => true, 'cost'=>$total], 201);

    }










    private function get_educational_charge($user_category){

        $educational_service_cost = ServiceCharge::select('doc_charge')
        ->where('category_user_id', '=', $user_category)
        ->where('doc_cat', '=', 'educational')
        ->first();
        return $educational_service_cost->doc_charge;



    }
    private function get_surcharge($school){

        $institution_sucharge = institutions::select('institutions.id', 'institutions.name', 'institution_charge')
        ->join('surcharge_models', 'surcharge_models.institution_id', '=', 'institutions.id')
        ->where('institutions.name', $school)
        ->first();

    // Check if $institution_sucharge is not null, or you can use the isNotEmpty() method
    if (!$institution_sucharge) {
        $surcharge = 0;
    } else {
        $surcharge = $institution_sucharge->institution_charge;
    }
return $surcharge;

    }
    private function get_professional_charge($user_category){
        $professional_service_cost =serviceCharge::select('doc_charge')->where('category_user_id','=',$user_category)
        ->where('doc_cat','=','professional')
        ->get();
        return $professional_service_cost;


    }


    private function get_financial_charge($user_category){

        $financial_service_cost = serviceCharge::select('doc_charge')
        ->where('category_user_id', '=', $user_category)
        ->where('doc_cat', '=', "financial")
        ->get();
return $financial_service_cost;

    }
}
