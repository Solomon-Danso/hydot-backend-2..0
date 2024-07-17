<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use App\Models\Sales;
use App\Models\AdminUser;
use App\Models\PriceConfiguration;
use App\Models\OurPortfolioProjects;
use App\Models\Customers;
use Carbon\Carbon;
use App\Models\Expenses;
use App\Mail\PaymentInvoice;
use Illuminate\Support\Facades\Log;


class Finance extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }



    function CreateSales(Request $req) {
        $a = AdminUser::where('UserId', $req->AdminId)->first();
        $c = Customers::where('UserId', $req->CustomerId)->first();
        $p = PriceConfiguration::where('ProductId', $req->ProductId)->first();
    
        if ($a == null) {
            return response()->json(['message' => 'Admin not found'], 400);
        }
    
        if ($c == null) {
            return response()->json(['message' => 'Customer not found'], 400);
        }
    
        if ($p == null) {
            return response()->json(['message' => 'Product not found'], 400);
        }
    
        $currentDate = Carbon::now();
        $newSubscriptionDays = ceil($req->Amount / $p->Amount);
    
        $existingToken = Sales::where('CustomerId', $c->UserId)
                      ->where('ProductId', $p->ProductId)
                      ->orderBy('created_at', 'desc')
                      ->first();

    
        if ($existingToken) {
           // return response()->json(['message' => 'Token Exist'], 400);
            Log::info('Existing token found:');
            Log::info($existingToken);
    
            $existingExpireDate = Carbon::parse($existingToken->ExpireDate);
            Log::info('existingExpireDate:');
            Log::info($existingExpireDate);

            $existingRemainingDays = $currentDate->diffInDays($existingExpireDate);
            Log::info('Existing RemainDays:');
            Log::info($existingRemainingDays);

            Log::info('newSubscriptionDays');
            Log::info($newSubscriptionDays);
            if($existingRemainingDays<0){
                $existingRemainingDays = 0;
            }
            
            $expireDate = $currentDate->addDays($existingRemainingDays+$newSubscriptionDays); // Extend expiry date by 500 days

        } else {
            $expireDate = $currentDate->copy()->addDays($newSubscriptionDays);
        }
    
        // Create a new sales record
        $s = new Sales();
        $s->Created_By_Id = $req->AdminId;
        $s->Created_By_Name = $a->Name;
        $s->TransactionId = $this->TokenGenerator();
        $s->ProductId = $p->ProductId;
        $s->ProductName = $p->ProductName;
        $s->CustomerId = $c->UserId;
        $s->CustomerName = $c->Name;
        $s->PricingType = $p->PricingType;
        $s->Amount = $req->Amount;
        $s->SubscriptionPeriodInDays = $newSubscriptionDays;
        $s->StartDate = $currentDate;
        $s->SystemDate = $currentDate;
        $s->ExpireDate = $expireDate;
    
        if ($req->filled('PaymentMethod')) {
            $s->PaymentMethod = $req->PaymentMethod;
        }
    
        if ($req->filled('PaymentReference')) {
            $s->PaymentReference = $req->PaymentReference;
        }
    
        $saver = $s->save();
    
        if ($saver) {
            $message = $c->Name . " made a payment of " . $req->Amount . " for " . $p->ProductName;
            $this->audit->Auditor($req->AdminId, $message);
            try {
                Mail::to($c->Email)->send(new PaymentInvoice($s));
                return response()->json(["message" => "Payment made Successfully"], 200);
            } catch (\Exception $e) {
                return response()->json(["message" => "Email Failed To Send"], 400);
            }
        } else {
            return response()->json(["Request" => "Failed"], 400);
        }
    }
    
    



function ViewSales(){
        return Sales::get();
}

function ViewOneSale(Request $req){
    $s = Sales::where("TransactionId",$req->TransactionId)->first();
    if($s==null){
        return response()->json(["message" => "Invalid Token, Try Again"], 400);
    }

    // Get current date as Carbon instance
    $currentDate = Carbon::now();

    // Update the 'CurrentDate' field in the CompanyToken model
    $s->CurrentDate = $currentDate;
    $s->save();

    if($s->PricingType == "One-time Purchase"){
        $message = $s->CustomerName."  payment transaction was viewed";
        $this->audit->Auditor($req->AdminId, $message);
        return $s;
    }else{

        if($currentDate > $c->ExpireDate){
            $message = $s->CustomerName."  payment transaction was viewed";
            $this->audit->Auditor($req->AdminId, $message);
            return response()->json(["message" => "Token has expired"], 400);
        }

        return $s;

    }



}

function RegenerateTransactionId(Request $req){
    $s = Sales::where('CustomerId', $req->CustomerId)->where('ProductId', $req->ProductId)->first();
    if($s==null){
        return response()->json(["message"=>"No Transaction Details Found"],400);
    }
    $c = Customers::where('UserId', $req->CustomerId)->first();
    if($c==null){
        return response()->json(["message"=>"No Customer Details Found"],400);
    }

    $s->TransactionId = $this->TokenGenerator();
    $saver = $s->save();

    if($saver){
        $message = $s->CustomerName."  payment transaction ID was regenerated";
        $this->audit->Auditor($req->AdminId, $message);
        try {
            Mail::to($c->Email)->send(new PaymentInvoice($s));
            return response()->json(["message" => "Operation was Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "Email Failed To Send"], 400);
        }
    } else {
        return response()->json(["Request" => "Failed"], 400);
    }



}




function ConfigurePrice(Request $req){

    $p =  OurPortfolioProjects::where('ProjectId',$req->ProductId)->first();
    if($p==null){
        return response()->json(["message"=>"Projects does not exist"],400);
    }

    $s = new PriceConfiguration();

    $s->Picture = $p->Picture;
    $s->ProductId = $p->ProjectId;
    $s->ProductName = $p->ProjectName;

    if($req->filled("Amount")){
        $s->Amount = $req->Amount;
    }

    if($req->filled("PricingType")){
        $s->PricingType = $req->PricingType;
    }

    $e = PriceConfiguration::where('ProductId',$req->ProductId)->first();
    if($e){
    $e->Picture = $p->Picture;
    $e->ProductId = $p->ProjectId;
    $e->ProductName = $p->ProjectName;

    if($req->filled("Amount")){
        $e->Amount = $req->Amount;
    }

    if($req->filled("PricingType")){
        $e->PricingType = $req->PricingType;
    }

    $saver = $e->save();
    if($saver){
        $message = $e->ProductName." price configured";

        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$e->ProductName." price configured successfully"],200);
    }
    else{
        return response()->json(["message"=>"Could not configure the price for this product"],400);
    }


    }else{

        $saver = $s->save();
        if($saver){
            $message = $s->ProductName." price configured";

            $this->audit->Auditor($req->AdminId, $message);
            return response()->json(["message"=>$s->ProductName." price configured successfully"],200);
        }
        else{
            return response()->json(["message"=>"Could not configure the price for this product"],400);
        }



    }






}



function GetAllPrice(Request $req){
     return PriceConfiguration::get();
}

function DeletePrice(Request $req){
    $s = PriceConfiguration::where('ProductId',$req->ProductId)->first();
    if($s==null){
        return response()->json(["message"=>"Products does not exist"],400);
    }

    $saver = $s->delete();
    if($saver){
        $message = $s->ProductName." price deleted";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$s->ProductName." price deleted successfully"],200);
    }
    else{
        return response()->json(["message"=>"Could not delete price"],200);

    }

}

function CreateExpenses(Request $req){
    $s = new Expenses();

    if($req->filled("Amount")){
        $s->Amount = $req->Amount;
    }

    if($req->filled("Reason")){
        $s->Reason = $req->Reason;
    }

    $saver = $s->save();
    if($saver){
        $message = "Expenses Posted Successfully";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=> $message ],200);
    }
    else{
        return response()->json(["message"=> "Expenses Posting Failed" ],400);
    }


}

function ViewExpenses(){
    return Expenses::get();
}

function CreateCalenderData(Request $req){

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



function IdGenerator(): string {
        $randomID = str_pad(mt_rand(1, 99999999), 30, '0', STR_PAD_LEFT);
        return $randomID;
}









}
