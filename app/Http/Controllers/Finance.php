<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use App\Models\Sales;
use App\Models\AdminUser;
use APP\Models\PriceConfiguration;
use App\Models\OurPortfolioProjects;
use App\Models\Customers;
use Carbon\Carbon;
use App\Models\Expenses;
use App\Mail\PaymentInvoice;



class Finance extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }

function CreateSales(Request $req){

        $a = AdminUser::where('UserId', $req->AdminId)->first();
        $c = Customers::where('UserId', $req->CustomerId)->first();
        $p = PriceConfiguration::where('ProductId', $req->ProductId)->first();

        if ($a==null) {
            return response()->json(['message' => 'Admin not found'], 400);
        }

        if ($c==null) {
            return response()->json(['message' => 'Customer not found'], 400);
        }

        if ($p==null) {
            return response()->json(['message' => 'Product not found'], 400);
        }

        $s = new Sales();

    

        $s->Created_By_Id = $req->AdminId;
        $s->Created_By_Name = $a->Name;  
        $s->TransactionId = $this->TokenGenerator();
        $s->ProductId = $p->ProductId;
        $s->ProductName = $p->ProductName;
        $s->CustomerId = $c->UserId;
        $s->CustomerName = $c->Name;
        $s->PricingType = $p->PricingType;

        if($req->filled("PaymentMethod")){
            $s->PaymentMethod = $req->PaymentMethod;
        }

        if($req->filled("PaymentReference")){
            $s->PaymentMethod = $req->PaymentMethod;
        }

        if($req->filled("Amount")){
            $s->Amount = $req->Amount;
        }

        $s->SubscriptionPeriodInDays = ceil($req->Amount / $p->Amount);
        $currentDate = Carbon::now();
    
        $s->StartDate = $currentDate;
        $s->SystemDate = $currentDate;

        $existingToken = Sales::where('CompanyId', $c->UserId)->where('ProductId', $p->ProductId)->first();
    
        if($existingToken){
            // Add the new subscription days to the remaining days of the existing subscription
            $existingExpireDate = Carbon::parse($existingToken->ExpireDate);
            $existingRemainingDays = $existingExpireDate->diffInDays($currentDate);
    
            $extendedExpireDate = $currentDate->copy()->addDays($s->SubscriptionPeriodInDays + $existingRemainingDays);
            $existingToken->ExpireDate = $extendedExpireDate;
            $saver=$existingToken->save();

            if($saver){

                $message = $s->CustomerName."  made a payment of ".$s->Amount." for ".$s->ProductName;
                $this->audit->Auditor($req->AdminId, $message);
        
                try {
                    Mail::to($c->Email)->send(new PaymentInvoice($existingToken));
                    return response()->json(["message" => $message], 200);
                } catch (\Exception $e) {
                    return response()->json(["message" => "Email Failed To Send"], 400);
                }
            } else {
                return response()->json(["Request" => "Failed"], 400);
            }


    
            // You may return a response here or perform additional actions as needed for the update of an existing subscription
        } else {
            $expireDate = $currentDate->copy()->addDays($s->SubscriptionPeriodInDays);
    
            $s->ExpireDate = $expireDate;
           
            $saver = $s->save();
    
            if($saver){
                $message = $s->CustomerName."  made a payment of ".$s->Amount." for ".$s->ProductName;
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

    if($s->PricingType == "Fixed"){
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
    $s = Sales::where('CompanyId', $req->CompanyId)->where('ProductId', $req->ProductId)->first();
    if($s==null){
        return response()->json(["message"=>"No Transaction Details Found"],400);
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

    $p =  OurPortfolioProjects::where('ProjectId',$req->ProjectId)->first();
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

function UpdatePrice(Request $req){

    $p =  OurPortfolioProjects::where('ProjectId',$req->ProductId)->first();
    if($p==null){
        return response()->json(["message"=>"Projects does not exist"],400);
    }

    $s = PriceConfiguration::where('ProductId',$req->ProductId)->first();
    if($s==null){
        return response()->json(["message"=>"Products does not exist"],400);
    }


    $s->Picture = $p->Picture;
    $s->ProductId = $p->ProjectId;
    $s->ProductName = $p->ProjectName;

    if($req->filled("Amount")){
        $s->Amount = $req->Amount;
    }

    if($req->filled("PricingType")){
        $s->PricingType = $req->PricingType;
    }

    $saver = $s->save();
    if($saver){
        $message = $s->ProductName." price updated";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$s->ProductName." price updated successfully"],200);
    }
    else{
        return response()->json(["message"=>"Could not update the price for this product"],400);
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
