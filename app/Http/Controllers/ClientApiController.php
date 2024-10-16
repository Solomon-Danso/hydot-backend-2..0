<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientApi;
use App\Models\Customers;
use App\Http\Controllers\AuditTrialController;
use App\Models\PackagePrice;
use App\Models\PrePaidMeter;


class ClientApiController extends Controller
{
    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }

    function CreateClientApiServerURL(Request $req){

        $s = Customers::where('UserId', $req->UserId)->first();
        if ($s==null) {
            return response()->json(['message' => 'Customer not found'], 400);
        }

        $p = PackagePrice::where('ProductId', $req->ProductId)->first();
        if ($s==null) {
            return response()->json(['message' => 'Product not found'], 400);
        }



        $c = new ClientApi();

            $c->apiHost = $req->apiHost;
            $c->ApiServerURL = $req->ApiServerURL;

            $c->CompanyId = $s->UserId;

            $c->CompanyName = $s->Name;

            $c->CompanyEmail = $s->Email;

            $c->CompanyPhone = $s->Phone;

            $c->productId = $p->ProductId;

            $c->productName = $p->ProductName;
            $c->packageType = $p->PackageType;

            $c->apiKey = $this->audit->TokenGenerator();
            $c->apiSecret = $this->audit->TokenGenerator();
            $c->softwareID = $this->audit->IdGeneratorLong();


        $saver= $c->save();
        if($saver){
            return response()->json(["message"=>"Setup Completed"], 200);
        }
        else{
            return response()->json(["message"=>"Setup Failed"], 400);
        }

    }

    function UpdateClientApiServerURL(Request $req){
        $c = ClientApi::where("CompanyId",$req->CompanyId)->first();

        if($c==null){
            return response()->json(["message"=>"ApiServerURL for this company not found"],400);
        }

        $p = PackagePrice::where('ProductId', $req->ProductId)->first();
        if ($p==null) {
            return response()->json(['message' => 'Product not found'], 400);
        }


        $c->packageType = $p->PackageType;

        if($req->filled("ApiServerURL")){
            $c->ApiServerURL = $req->ApiServerURL;
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
        $c = ClientApi::where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"Company not found"],400);
        }

       return $c;
    }

    function DeleteClientApiServerURL(Request $req){
        $c = ClientApi::where("CompanyId",$req->CompanyId) ->first();

        if($c==null){
            return response()->json(["message"=>"Company not found"],400);
        }

        $saver= $c->delete();
        if($saver){
            return response()->json(["message"=>"Api Deleted Successfully"], 200);
        }
        else{
            return response()->json(["message"=>"Api Deletion Failed"], 400);
        }
    }

    function ViewAllClientApiServerURL()
{
    // Retrieve all data from ClientApi and PrePaidMeter
    $clients = ClientApi::get();
    $prepaid = PrePaidMeter::get();

    // Combine the clients with their matching prepaid data based on ProductId
    $combined = $clients->map(function ($client) use ($prepaid) {
        // Find the matching PrePaidMeter record with the same ProductId
        $matchingPrepaid = $prepaid->firstWhere('softwareID', $client->softwareID);

        // Add the ExpireDate to the client if a match is found
        if ($matchingPrepaid) {
            $client->ExpireDate = $matchingPrepaid->ExpireDate; // Update to use ExpireDate
        }

        return $client;
    });

    return $combined;
}






}
