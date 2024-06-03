<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientApi;

class ClientApiController extends Controller
{
    function CreateClientApiServerURL(Request $req){
        $c = new ClientApi();

        if($req->filled("CompanyId")){
            $c->CompanyId = $req->CompanyId;
        }

        if($req->filled("CompanyName")){
            $c->CompanyName = $req->CompanyName;
        }

        if($req->filled("CompanyEmail")){
            $c->CompanyEmail = $req->CompanyEmail;
        }

        if($req->filled("CompanyPhone")){
            $c->CompanyPhone = $req->CompanyPhone;
        }

        if($req->filled("ApiServerURL")){
            $c->ApiServerURL = $req->ApiServerURL;
        }

        if($req->filled("ApiMediaURL")){
            $c->ApiMediaURL = $req->ApiMediaURL;
        }



        $saver= $c->save();
        if($saver){
            return response()->json(["message"=>"Api Created Successfully"], 200);
        }
        else{
            return response()->json(["message"=>"Api Creation Failed"], 400);
        }

    }

    function UpdateClientApiServerURL(Request $req){
        $c = ClientApi::where("id", $req->id) ->where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }

        if($req->filled("CompanyId")){
            $c->CompanyId = $req->CompanyId;
        }

        if($req->filled("CompanyName")){
            $c->CompanyName = $req->CompanyName;
        }

        if($req->filled("ApiMediaURL")){
            $c->ApiMediaURL = $req->ApiMediaURL;
        }

        if($req->filled("CompanyEmail")){
            $c->CompanyEmail = $req->CompanyEmail;
        }

        if($req->filled("CompanyPhone")){
            $c->CompanyPhone = $req->CompanyPhone;
        }

        if($req->filled("ApiServerURL")){
            $c->ApiServerURL = $req->ApiServerURL;
        }

        $saver= $c->save();
        if($saver){
            return response()->json(["message"=>"Api Created Successfully"], 200);
        }
        else{
            return response()->json(["message"=>"Api Creation Failed"], 400);
        }

    }


    function ViewClientApiServerURL(Request $req){
        $c = ClientApi::where("id", $req->id) ->where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }

       return $c;
    }

    function DeleteClientApiServerURL(Request $req){
        $c = ClientApi::where("id", $req->id) ->where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }

        $saver= $c->delete();
        if($saver){
            return response()->json(["message"=>"Api Deleted Successfully"], 200);
        }
        else{
            return response()->json(["message"=>"Api Deletion Failed"], 400);
        }
    }

    function ViewAllClientApiServerURL(){
       return ClientApi::all();
    }

}
