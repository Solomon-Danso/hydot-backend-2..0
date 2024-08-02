<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\GlobalPayment;
use Illuminate\Support\Facades\Http;
use App\Models\Sales;
use App\Http\Controllers\AuditTrialController;
use Paystack;
use Illuminate\Support\Facades\Mail;
use App\Mail\HydotPay;


class GlobalPaymentController extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }



public function AddPayment(Request $req){

$s = new GlobalPayment();


$options = ["tref", "ProductId", "Product", "Username", "Amount", "SuccessApi", "CallbackURL"];

foreach ($options as $option) {

    if($req->filled($option)){
        $s->$option = $req->$option;
    }
}

$s->IsExecuted = false;
$s->save();

}




public function GetPayment(Request $req){
    $s = GlobalPayment::where("tref", $req->tref)->first();

    if ($s == null) {
        return response()->json(["message" => "Payment Not Found"], 400);
    }

    if ($s->IsExecuted == true) {
        return response()->json(["message" => "Payment already recorded"], 400);
    }

    $successApiUrl = $s->SuccessApi;

    try {
        $response = Http::timeout(60)->get($successApiUrl); // Increase timeout to 60 seconds
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        return response()->json(["message" => "Failed to execute Success API: " . $e->getMessage()], 500);
    }

    if ($response->failed()) {
        return response()->json(["message" => "Failed to execute Success API"], 400);
    }

    $s->IsExecuted = true;
    $s->save();

    return response()->json(["message" => $s->CallbackURL], 200);
}


public function TestPayment(){
    return "Payment Api is working";
}

public function SchedulePayment(Request $req){

    $stu = AdminUser::where('UserId', $req->AdminId)->first();
    if ($stu == null) {
        return response()->json(["message" => "Admin does not exist"], 400);
    }


    $s = new Sales();
    $sections = [ "CustomerName", "CustomerId", "PaymentReference","Amount"];

    foreach($sections as $sec){
        if($req->filled($sec)){
            $s-$sec = $req->$sec;
        }
    }

    $s->Created_By_Id = $stu->UserId;
    $s->Created_By_Name = $stu->Name;
    $s->TransactionId = $this->TokenGenerator();
    $s->IsApproved = false;

    $saver = $s->save();
    if($saver){

        Mail::to($s->CustomerId)->send(new HydotPay($s));

        $message = "Scheduled payment for ".$s->Created_By_Name;
        $this->audit->Auditor($req->AdminId, $message);


        return response()->json(["message" => $message], 200);

    }else{
        return response()->json(["message" => "Failed to schedule payment"], 400);
    }


}

public function MakePayment($TransactionId)
{

    $sales = Sales::where("TransactionId",$TransactionId)->first();
    if(!$sales){
        return response()->json(["message" => "Transaction not found"], 400);
    }

        // Ensure the total amount is an integer and in the smallest currency unit (e.g., kobo, pesewas)
        $totalInPesewas = intval($sales->Amount * 100);

        $tref = Paystack::genTranxRef();

        $saver = $s->save();
        if ($saver) {


            $response = Http::post('https://mainapi.hydottech.com/api/AddPayment', [
                'tref' =>  $tref,
                'ProductId' => "hdtCollection",
                'Product' => 'Manual Collection',
                'Username' => $sales->CustomerName,
                'Amount' => $sales->Amount,
                'SuccessApi' => 'https://mainapi.hydottech.com/api/ConfirmPayment/'.$tref,
                //'SuccessApi' => 'https://hydottech.com',
                'CallbackURL' => 'https://hydottech.com',
            ]);

            if ($response->successful()) {

                $paystackData = [
                    "amount" => $totalInPesewas, // Amount in pesewas
                    "reference" => $tref,
                    "email" => $sales->CustomerId,
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


function ConfirmPayment($RefId)
{
    $sales = Sales::where("TransactionId",$RefId)->first();
    if(!$sales){
        return response()->json(["message" => "Payment not found"], 400);
    }
    $sales->IsApproved = true;
    $saver = $sales->save();

    if($saver){
        return response()->json(["message" => "Payment confirmed successfully"], 200);
    }
    else{
        return response()->json(["message" => "Failed to confirm payment"], 200);
    }


}

function GetSpecificUnApprovedPayment(Request $req){

    $sales = Sales::where("TransactionId",$req->TransactionId)->first();
    if(!$sales){
        return response()->json(["message" => "Payment not found"], 400);
    }

    return $sales;


}






function TokenGenerator(): string {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$^&*()_+{}|<>-=[],.';
    $length = 30;
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}





}
