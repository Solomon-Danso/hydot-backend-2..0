<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuditTrialController;
use App\Models\PackagePrice;
use App\Models\ClientApi;
use App\Models\PrePaidMeter;
use Carbon\Carbon;
use App\Mail\Subscription;
use Illuminate\Support\Facades\Mail;
use App\Models\Sales;
use Paystack;
use App\Mail\HcsPay;


class HCSController extends Controller
{

    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }


    public function ConfigurePackage(Request $req){

        $s = new PackagePrice();

        $s->ProductId = $this->audit->IdGenerator();
        $field = ["ProductName", "PackageType", "VariableCost"];

        foreach($field as $f){

            if($req->filled($f)){
                $s->$f = $req->$f;
            }

        }

        $saver = $s->save();

        if($saver){

            $resMessage ="Package Configured for  ".$s->ProductName." successfully";
            $adminMessage = "Package Configured for  ".$s->ProductName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to configure products"], 400);
        }



    }



    public function DeleteConfigurePackage(Request $req){

        $s = PackagePrice::where("ProductId", $req->ProductId)->first();
        if($s == null){
            return response()->json(["message" => "Product does not exist"], 400);

        }

        $saver = $s->delete();

        if($saver){

            $resMessage ="Package Configuration deleted for  ".$s->ProductName." successfully";
            $adminMessage = "Package Configuration deleted for  ".$s->ProductName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to delete products"], 400);
        }



    }

    public function ViewConfigurePackage(Request $req){

        $s = PackagePrice::where("ProductId", $req->ProductId)->first();
        if($s == null){
            return response()->json(["message" => "Product does not exist"], 400);

        }

        return $s;

    }

    public function ViewAllConfigurePackage(Request $req){

        $s = PackagePrice::get();

        return $s;

    }


public function SubscribeToken($softwareID, $Amount)
{
    // Find the client with the specified softwareID
    $c = ClientApi::where("softwareID", $softwareID)->first();

    if ($c == null) {
        return response()->json(["message" => "Company not found"], 400);
    }

    // Find the package price for the specified ProductId and PackageType
    $p = PackagePrice::where("ProductId", $c->productId)
                     ->where("PackageType", $c->packageType)
                     ->first();

    if ($p == null) {
        return response()->json(["message" => "Package not found"], 400);
    }

    // Check if a PrePaidMeter entry already exists with the given softwareID, ProductId, PackageType, and companyId
    $existingEntry = PrePaidMeter::where('softwareID', $c->softwareID)
                                 ->where('ProductId', $p->ProductId)
                                 ->where('PackageType', $p->PackageType)
                                 ->where('companyId', $c->CompanyId)
                                 ->first();

    // Calculate the number of days the subscription should last
    $newDays = (int) floor($Amount / $p->VariableCost); // Ensure 'newDays' is an integer

    if ($existingEntry) {
        // Calculate the remaining days until the current ExpireDate
        $currentDate = Carbon::now();
        $remainingDays = $currentDate->diffInDays(Carbon::parse($existingEntry->ExpireDate), false); // Use false to allow negative values
        // Calculate the new expiry date by adding remaining days and new days
        $totalDays = max(0, $remainingDays) + $newDays; // Ensure remaining days are non-negative
        $existingEntry->ExpireDate = $currentDate->addDays($totalDays);
        $existingEntry->Token = $this->audit->IdGeneratorLong();


        $saver =  $existingEntry->save();
        if($saver){

            try {
                Mail::to($c->CompanyEmail)->send(new Subscription($existingEntry));
                return response()->json(["message" => "Subscription is successful, Check your email for the subscription number"], 200);
            } catch (\Exception $e) {

                return response()->json(["message" => "Email Failed"], 400);
            }


        }else{
            return response()->json(["message" => "Subscription Failed"], 400);
        }


    } else {
        // Create a new PrePaidMeter entry if no existing entry is found
        $s = new PrePaidMeter();
        $s->Token = $this->audit->IdGeneratorLong();
        $s->ProductId = $p->ProductId;
        $s->ProductName = $p->ProductName;
        $s->PackageType = $p->PackageType;
        $s->Amount = $req->Amount;
        $s->apiHost = $c->apiHost;
        $s->apiKey = $c->apiKey;
        $s->softwareID = $c->softwareID;
        $s->companyId = $c->CompanyId;

        // Set the ExpireDate based on the current date plus the calculated number of new days
        $s->ExpireDate = Carbon::now()->addDays($newDays);

        $saver = $s->save();
        if($saver){

            try {
                Mail::to($c->CompanyEmail)->send(new Subscription($s));
                return response()->json(["message" => "Subscription is successful, Check your email for the subscription number"], 200);
            } catch (\Exception $e) {

                return response()->json(["message" => "Email Failed"], 400);
            }


        }else{
            return response()->json(["message" => "Subscription Failed"], 400);
        }




    }
}

 public function GetToken($Token){

    $p = PrePaidMeter::where('Token', $Token)->first();
    if($p==null){
        return response()->json(["message" => "Invalid Token"], 400);
    }

  return $p;

}

  public function GetAllToken(){
    $p = PrePaidMeter::get();
       return $p;

}


//User will trigger this method from Spring Boot external application
public function HCSSchedulePayment($softwareID, $Amount){

    $c = ClientApi::where("softwareID", $softwareID)->first();

    if ($c == null) {
        return response()->json(["message" => "Company not found"], 400);
    }



    $s = new Sales();

    $s->CustomerName = $c->CompanyEmail;
    $s->CustomerId = $softwareID;
    $s->PaymentReference = "Software Subscription for ".$c->productName;
    $s->Amount = $Amount;
    $s->TransactionId = $this-> IdGenerator();
    $s->IsApproved = false;

    $saver = $s->save();
    if($saver){

        Mail::to($c->CompanyEmail)->send(new HcsPay($s));

        $message = "Scheduled payment for ".$s->CustomerName;
        $this->audit->Auditor($req->AdminId, $message);


        return response()->json(["message" => $message], 200);

    }else{
        return response()->json(["message" => "Failed to schedule payment"], 400);
    }


}


//System will trigger HCSMakePayment from the email click
public function HCSMakePayment($TransactionId,$softwareID, $Amount)
{
    $sales = Sales::where("TransactionId", $TransactionId)->first();
    if (!$sales) {
        return response()->json(["message" => "Transaction not found"], 400);
    }

    // Ensure the total amount is an integer and in the smallest currency unit (e.g., kobo, pesewas)
    $totalInPesewas = intval($sales->Amount * 100);

    $tref = Paystack::genTranxRef();

    $saver = $sales->save();
    if ($saver) {
        $response = Http::post('https://mainapi.hydottech.com/api/AddPayment', [
            'tref' =>  $TransactionId,
            'ProductId' => "hcsCollection",
            'Product' => 'Manual Collection',
            'Username' => $sales->CustomerName,
            'Amount' => $sales->Amount,
            'SuccessApi' => 'https://mainapi.hydottech.com/api/SubscribeToken/' . $softwareID.'/'.$Amount,
            //'SuccessApi' => 'https://hydottech.com',
            'CallbackURL' => 'https://hydottech.com',
        ]);

        if ($response->successful()) {
            // Function to validate an email address
            $c = ClientApi::where("softwareID", $softwareID)->first();
            if($c==null){
                return response()->json(["message","Company not found"],400);
            }


            $paystackData = [
                "amount" => $totalInPesewas, // Amount in pesewas
                "reference" => $TransactionId,
                "email" => $c->CompanyEmail,
                "currency" => "GHS",
            ];

            return Paystack::getAuthorizationUrl($paystackData)->redirectNow();
        } else {
            return response()->json(["message" => "External Payment Api is down"], 400);
        }
    } else {
        return response()->json(["message" => "Failed to initialize payment"], 400);
    }
}










}
