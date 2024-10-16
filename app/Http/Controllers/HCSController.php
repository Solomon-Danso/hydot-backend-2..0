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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

    public function UpdateConfigurePackage(Request $req){

        $s = PackagePrice::where("ProductId",$req->ProductId)->first();
        if($s == null){
            return response()->json(["message" => "Invalid Package Type"], 400);
        }

        if($req->VariableCost<1){
            return response()->json(["message" => "Price cannot be less than 1"], 400);
        }

        $s->VariableCost = $req->VariableCost;

        $saver = $s->save();

        if($saver){

            $resMessage ="Package Updated for  ".$s->ProductName." successfully";
            $adminMessage = "Package Updated for  ".$s->ProductName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to update package price"], 400);
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
        // Get the base URL of the application
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = rtrim($protocol . $host . $scriptName, '/'); // This will give you the base URL of the app

        // Find the client with the specified softwareID
        $c = ClientApi::where("softwareID", $softwareID)->first();

        if ($c == null) {
            return response()->json(["status" => "Failed", "message" => "Company not found"], 400);
        }

        // Find the package price for the specified ProductId and PackageType
        $p = PackagePrice::where("ProductId", $c->productId)
                         ->where("PackageType", $c->packageType)
                         ->first();

        if ($p == null) {
            return response()->json(["status" => "Failed", "message" => "Package not found"], 400);
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
            $finalExpireDate = $currentDate->addDays($totalDays)->toDateString();
            $existingEntry->ExpireDate = $finalExpireDate;
            $existingEntry->Token = $this->audit->IdGeneratorLong();

            $saver = $existingEntry->save();
            if ($saver) {
                // Send a POST request to the external API server
                try {
                    $response = Http::withHeaders([
                            'Origin' => $baseUrl,
                            'Referer' => $baseUrl,

                        ])
                        ->asForm()
                        ->post($c->ApiServerURL . 'api/audit/TopUp', [
                            'apiHost' => $c->apiHost,
                            'companyId' => $c->CompanyId,
                            'productId' => $c->productId,
                            'packageType' => $c->packageType,
                            'softwareID' => $c->softwareID,
                            'expireDate' => $finalExpireDate,
                        ]);


                    // Return the response from the API in the specified format
                    return response()->json($response->json(), $response->status());
                } catch (\Exception $e) {

                    return response()->json(["Status" => "Failed", "Message" => "Failed to connect to external server: " . $e->getMessage()], 400);
                }
            } else {
                return response()->json(["Status" => "Failed", "Message" => "Subscription Failed"], 400);
            }
        } else {
            // Create a new PrePaidMeter entry if no existing entry is found
            $s = new PrePaidMeter();
            $s->Token = $this->audit->IdGeneratorLong();
            $s->ProductId = $p->ProductId;
            $s->ProductName = $p->ProductName;
            $s->PackageType = $p->PackageType;
            $s->Amount = $Amount;
            $s->apiHost = $c->apiHost;
            $s->apiKey = $c->apiKey;
            $s->softwareID = $c->softwareID;
            $s->companyId = $c->CompanyId;

            // Set the ExpireDate based on the current date plus the calculated number of new days
            $finalExpireDate = Carbon::now()->addDays($newDays)->toDateString();
            $s->ExpireDate = $finalExpireDate;

            $saver = $s->save();
            if ($saver) {
                // Send a POST request to the external API server
                try {
                    $response = Http::withHeaders([
                            'Origin' => $baseUrl,
                            'Referer' => $baseUrl,
                        ])
                        ->asForm()
                        ->post($c->ApiServerURL . 'api/audit/TopUp', [
                            'apiHost' => $c->apiHost,
                            'companyId' => $c->CompanyId,
                            'productId' => $p->ProductId,
                            'packageType' => $p->PackageType,
                            'softwareID' => $c->softwareID,
                            'expireDate' => $finalExpireDate,
                        ]);

                    // Return the response from the API in the specified format
                    return response()->json(["message"=>"Success"],200);
                } catch (\Exception $e) {
                    return response()->json(["Status" => "Failed", "Message" => "Failed to connect to external server: " . $e->getMessage()], 400);
                }
            } else {
                return response()->json(["Status" => "Failed", "Message" => "Subscription Failed"], 400);
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
public function HCSSchedulePayment($softwareID, $Amount)
{
    $c = ClientApi::where("softwareID", $softwareID)->first();

    if ($c == null) {
        return response()->json(["message" => "Company not found"], 400);
    }


    if($Amount<1){
        return response()->json(["message" => "Amount cannot be less than 1"], 400);
    }


    $p = PackagePrice::where("ProductId", $c->productId)
        ->where("PackageType", $c->packageType)
        ->first();

    if ($p == null) {
        return response()->json(["message" => "Package not found"], 400);
    }

    // Calculate the number of days based on the amount and the variable cost
    $newDays = intdiv($Amount, $p->VariableCost); // Get the integer division of the amount
    $remainder = $Amount % $p->VariableCost; // Calculate the remaining amount

    // Check if the user can subscribe for more days if they top up
    if ($remainder > 0) {
        $topUpAmount = $p->VariableCost - $remainder;
        $acceptAmount = $Amount - $remainder;

        $message = "The daily cost for the {$c->packageType} package is {$p->VariableCost} cedis.Based on your payment of {$Amount} cedis, you will be subscribed for {$newDays} days.To extend your subscription to an additional day, you may top up with {$topUpAmount} cedis, or you can maintain your a payment of {$acceptAmount} cedis to subscribe for {$newDays} days.";
        return response()->json(["message" => $message], 400);
    }

    $s = new Sales();
    $s->CustomerName = $c->CompanyEmail;
    $s->CustomerId = $softwareID;
    $s->PaymentReference = "Software Subscription for " . $c->productName;
    $s->Amount = $Amount;
    $s->TransactionId = $this->audit->IdGenerator();
    $s->IsApproved = false;

    $saver = $s->save();
    if ($saver) {


        Mail::to($c->CompanyEmail)->send(new HcsPay($s, $p->ProductName, $p->PackageType, $newDays));

        $message = "The daily cost for the {$c->packageType} package is {$p->VariableCost} cedis. Based on your payment of {$Amount} cedis, you will be subscribed for {$newDays} days. Please check your email ({$c->CompanyEmail}) to approve this transaction.";

        return response()->json(["message" => $message], 200);

    } else {
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
