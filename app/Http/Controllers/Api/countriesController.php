<?php

namespace App\Http\Controllers\Api;
use App\Models\countries;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class CountriesController extends Controller
{
    //


    public function getAll(request $request){

        if($request->input('access')===('docs_verify_frontend')){
            //get all countries
            $countries =countries::select('id','name')->get();
//Remember to implement md5 encryption;

            return response()->json(['message' => 'Successful', 'data'=>$countries],200);

            //it is from our frontend
        }else{

dd($request->all());
            //it is not from our frontend
            return response()->json(['message' => 'Invalid Access'],400);
        }
    }

}
