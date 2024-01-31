<?php
namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Institutions;
use App\Models\ServiceCharge;
use Illuminate\Support\Facades\Auth;

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
    }
}
