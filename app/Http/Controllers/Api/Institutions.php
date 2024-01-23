<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\institutions as ModelsInstitutions;
use Illuminate\Http\Request;

class Institutions extends Controller
{
    //
    public function getAllInstitution(){
        $get_all_inst = ModelsInstitutions::select('name')->where('country_id', '=',120)->get();
        return response(['data' => $get_all_inst, 'success'=>true
    ], 200);
    }
}
