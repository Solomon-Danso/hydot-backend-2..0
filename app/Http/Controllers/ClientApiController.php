<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientApi;
use App\Models\Customers;

class ClientApiController extends Controller
{
    function CreateClientApiServerURL(Request $req){

        $s = Customers::where('UserId', $req->CustomerId)->first();
        if ($s==null) {
            return response()->json(['message' => 'Customer not found'], 400);
        }


        $c = new ClientApi();


            $c->CompanyId = $s->UserId;

            $c->CompanyName = $s->Name;

            $c->CompanyEmail = $s->Email;

            $c->CompanyPhone = $s->Phone;


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
        $c = ClientApi::where("CompanyId",$req->CompanyId) -> where("id", $req->Id)->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }



        if($req->filled("ApiServerURL")){
            $c->ApiServerURL = $req->ApiServerURL;
        }
        if($req->filled("ApiMediaURL")){
            $c->ApiMediaURL = $req->ApiMediaURL;
        }

        $saver= $c->save();
        if($saver){
            return response()->json(["message"=>"Api Updated Successfully"], 200);
        }
        else{
            return response()->json(["message"=>"Api Update Failed"], 400);
        }

    }


    function ViewClientApiServerURL(Request $req){
        $c = ClientApi::where("id", $req->Id) ->where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }

       return $c;
    }

    function DeleteClientApiServerURL(Request $req){
        $c = ClientApi::where("id", $req->Id) ->where("CompanyId",$req->CompanyId) ->first();

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
