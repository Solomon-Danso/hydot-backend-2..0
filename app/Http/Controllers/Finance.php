<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use App\Models\Sales;
use App\Models\AdminUser;

class Finance extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }

    function CreateSales(Request $req){

        $a = AdminUser::where('UserId', $req->AdminId)->first();

        if ($a==null) {
            return response()->json(['message' => 'Admin not found'], 400);
        }

        $s = new Sales();

        if ($a->Role==="SuperAdmin") {
            
            $s->Created_By_Id = $req->AdminId;
            $s->Created_By_Name = $a->Name;
            
            $s->Approved_By_Id = $req->AdminId;
            $s->Approved_By_Name = $a->Name;

            $s->IsApproved = true;

        }
        else{

            $s->Created_By_Id = $req->AdminId;
            $s->Created_By_Name = $a->Name;
    
    

        }

        $s->IsApproved = false;
        $s->TransactionId = $this->IdGenerator();


       
        $fields = [
            "ProductId","ProductName","CustomerId","CustomerName",
            "PaymentMethod", "PaymentReference", "Amount",     
    ];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    }

    $saver = $s->save();
    if($saver){

        $message = $s->Amount." was paid by ".$s->CompanyName;
        $this->audit->Auditor($req->AdminId, $message);
/*
TODO:
1. Send a professional invoice receipt through company Email
2. Sales should not be editable and deletable 
3. If Role is not SuperAdmin, then sales needs to go for approval
*/

        return response()->json(["message" => $message], 200);

    }else{
        return response()->json(["message" => "Payment Failed"], 400);
    }




    }


    function ApproveSales(Request $req){

        $a = AdminUser::where('UserId', $req->AdminId)->where("Role", "SuperAdmin")->first();

        if ($a==null) {
            return response()->json(['message' => 'Admin not found'], 400);
        }

        $s = Sales::where('TransactionId', $req->TransactionId)->first();

        if ($s==null) {
            return response()->json(['message' => 'TransactionId does not exist'], 400);
        }

        $s->Approved_By_Id = $req->AdminId;
        $s->Approved_By_Name = $a->Name;

        $s->IsApproved = true;

        $saver = $s->save();
        if($saver){
    
            $message = $s->Amount." was paid by ".$s->CompanyName;
            $this->audit->Auditor($req->AdminId, $message);
    /*
    TODO:
    1. Send a professional invoice receipt through company Email
    2. Sales should not be editable and deletable 
    3. If Role is not SuperAdmin, then sales needs to go for approval
    */
    
            return response()->json(["message" => $message], 200);
    
        }else{
            return response()->json(["message" => "Payment Failed"], 400);
        }




    }


    function ViewSales(){
        return Sales::get();
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
