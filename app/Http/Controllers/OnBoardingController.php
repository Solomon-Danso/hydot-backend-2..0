<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuditTrialController;
use Illuminate\Support\Facades\Mail;
use App\Models\Customers;
use App\Models\OnBoarding;
use App\Models\Sales;
use App\Models\Meetings;
use App\Mail\Meeting;
use App\Models\DeBoarding;
use App\Mail\HydotPay;
use App\Models\BulkSender;
use App\Models\Partner;


class OnBoardingController extends Controller
{
    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }


    public function OnBoard(Request $req){

        $c = Sales::where("TransactionId",$req->TransactionId)->first();
        if(!$c){
            return response()->json(["message"=>"Sales not found"],400);
        }

        $a = Customers::where('UserId', $c->CustomerId)->first();
        if(!$a){
            return response()->json(["message"=>"Customer not found"],400);
        }


        $s = new OnBoarding();
        $s->Created_By_Id =  $c->Created_By_Id;
        $s->Created_By_Name = $c->Created_By_Name;
        $s->TransactionId =   $c->TransactionId;
        $s->ProductId = $c->ProductId;
        $s->ProductName = $c->ProductName;
        $s->CustomerId = $c->CustomerId;
        $s->CustomerName = $c->CustomerName;
        $s->CustomerEmail = $a->Email;
        $s->PricingType = $c->PricingType;
        $s->Amount = $c->Amount;
        $s->PaymentReference = $c->PaymentReference;


        $saver = $s->save();

        if($saver){
            $c->IsOnboardClicked = true;
            $c->save();

            $resMessage ="Onboarding Initialized for  ".$c->CustomerName.",  awaiting first meeting";
            $adminMessage = "Completed Onboarding initialization for ".$c->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to initialize onboarding"], 400);
        }



    }

    public function GetStep1Members(Request $req){
        $s = OnBoarding::where("Step1Completed",false)
        ->get();
        return $s;
    }

    public function ScheduleMeeting(Request $req){



        $s = new Meetings();

        if($req->Target=="Individual"){

            $fields = ["Name","Email", "Link","Time","Reason"];

            foreach($fields as $field){
                if($req->filled($field)){
                    $s->$field = $req->$field;
                }
            }

            $saver = $s->save();
            if($saver){

                try {
                    Mail::to($s->Email)->send(new Meeting($s));
                    $resMessage ="Meeting with ".$s->Name." scheduled successfully";
                    $adminMessage = "Scheduled Meeting with ".$s->Name;
                    $this->audit->Auditor($req->AdminId, $adminMessage);

                    return response()->json(["message"=>$resMessage],200);
                } catch (\Exception $e) {

                    return response()->json(['message' => 'Email request failed: ' . $e->getMessage()], 400);
                }


            }else{
                return response()->json(["message"=>"Resources sending failed"],400);
            }




        }

        if ($req->Target=="Group"){


                $partners = BulkSender::pluck('Email');

                $worked = false;
                foreach($partners as $partner){


            $fields = [ "Link","Time","Reason"];

            foreach($fields as $field){
                if($req->filled($field)){
                    $s->$field = $req->$field;
                }
            }
            $s->Email = $partner;
            $s->Name = $partner;
            $s->save();


                    try {
                        Mail::to($partner)->send(new Meeting($s));
                        $worked = true;
                        $d = BulkSender::where("Email", $partner)->first();
                        $d->delete();
                    // return response()->json(["message" => "Resource sent successfully"]);
                    } catch (\Exception $e) {

                        return response()->json(['message' => 'Email request failed: ' . $e->getMessage()], 400);
                    }



                }

                if($worked){
                    return response()->json(["message"=>"Resources sent successfully"],200);
                }
                else{
                    return response()->json(["message"=>"Resources sending failed"],400);
                }








}

if ($req->Target=="Partners"){


    $partners = Partner::where("IsBlocked",false)->get();

    $worked = false;
    foreach($partners as $partner){


    $fields = [ "Link","Time","Reason"];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    }
    $s->Email = $partner->Email;
    $s->Name = $partner->Name;
    $s->save();


            try {
                Mail::to($partner->Email)->send(new Meeting($s));
                $worked = true;

            // return response()->json(["message" => "Resource sent successfully"]);
            } catch (\Exception $e) {

                return response()->json(['message' => 'Email request failed: ' . $e->getMessage()], 400);
            }



        }

        if($worked){
            return response()->json(["message"=>"Schedule sent successfully"],200);
        }
        else{
            return response()->json(["message"=>"Schedule sending failed"],400);
        }








}





    }

    public function GetAllMeeting(Request $req){

        $s = Meetings::orderBy("created_at", "desc")->get();
        return $s;

    }



    public function FirstMeeting(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->FirstMeeting = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="First meeting with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed First meeting with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete first meeting"], 400);
        }






    }

    public function CompleteStep1(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }


        if($s->FirstMeeting != true){
            return response()->json(["message"=>"First meeting not completed"],400);
        }




        $s->Step1Completed = true;
        $s->Step1AdminId = $req->AdminId;

        $saver = $s->save();
        if($saver){
            $resMessage ="Stage 1 completed successfully";
            $adminMessage = "Completed Stage 1 successfully".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete step 1"], 400);
        }






    }


    public function GetStep2Members(Request $req){
        $s = OnBoarding::where("Step2Completed",false)
        ->get();
        return $s;
    }

    public function MOUAgreement(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->MOUAgreement = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="MOU explanation with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed MOU explanation with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete MOU explanation"], 400);
        }






    }


    public function DomainAndHosting(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->DomainAndHosting = true;

        $saver = $s->save();
        if($saver){

            $this->AuthorizePayment($req->AdminId, $req->TransactionId);


            $resMessage ="Domain And Hosting with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed Domain And Hosting with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Domain And Hosting"], 400);
        }






    }


    function AuthorizePayment($AdminId,$TransactionId ) {


        $c = Sales::where("TransactionId",$TransactionId)->first();
        if(!$c){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s = OnBoarding::where("TransactionId",$TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }


        $saver = $s->save();

        if ($saver) {
            Mail::to($s->CustomerEmail)->send(new HydotPay($c));

            $message = "Scheduled payment for ".$s->CustomerName;
            $this->audit->Auditor($AdminId, $message);


            return response()->json(["message" => $message], 200);

        } else {
            return response()->json(["Request" => "Failed"], 400);
        }
    }


    public function PaymentCompleted(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $sales = Sales::where("TransactionId",$req->TransactionId)->first();
        if(!$sales){
            return response()->json(["message" => "Payment not found"], 400);
        }

        $s->PaymentCompleted = $sales->IsApproved;

         $s->save();
        if($s->PaymentCompleted == true){
            $resMessage ="Payment with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed Payment with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Payment not completed"], 400);
        }






    }

    public function CompleteStep2(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }


        if($s->MOUAgreement != true){
            return response()->json(["message"=>"MOU Agreement not completed"],400);
        }

        if($s->DomainAndHosting != true){
            return response()->json(["message"=>"Domain And Hosting not completed"],400);
        }

        if($s->PaymentCompleted != true){
            return response()->json(["message"=>"Payment not completed"],400);
        }

        if($s->FirstMeeting != true){
            return response()->json(["message"=>"First meeting not completed"],400);
        }




        $s->Step2Completed = true;
        $s->Step2AdminId = $req->AdminId;

        $saver = $s->save();
        if($saver){
            $resMessage ="Stage 2 completed successfully";
            $adminMessage = "Completed Stage 2 successfully".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete step 3"], 400);
        }






    }


    public function GetStep3Members(Request $req){
        $s = OnBoarding::where("Step3Completed",false)
        ->get();
        return $s;
    }

    public function SoftwareUpload(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->SoftwareUpload = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="Software Upload with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed Software Upload with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Software Upload"], 400);
        }






    }

    public function ThirdPartyServices(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->ThirdPartyServices = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="Third Party Services with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed Third Party Services with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Third Party Services"], 400);
        }






    }

    public function Testing(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->Testing = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="Testing with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed Testing with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Testing"], 400);
        }






    }

    public function CompleteStep3(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }


        if($s->SoftwareUpload != true){
            return response()->json(["message"=>"Software Upload not completed"],400);
        }

        if($s->ThirdPartyServices != true){
            return response()->json(["message"=>"Third Party Services not completed"],400);
        }

        if($s->Testing != true){
            return response()->json(["message"=>"Testing not completed"],400);
        }

        if($s->MOUAgreement != true){
            return response()->json(["message"=>"MOU Agreement not completed"],400);
        }

        if($s->DomainAndHosting != true){
            return response()->json(["message"=>"Domain And Hosting not completed"],400);
        }

        if($s->PaymentCompleted != true){
            return response()->json(["message"=>"Payment not completed"],400);
        }

        if($s->FirstMeeting != true){
            return response()->json(["message"=>"First meeting not completed"],400);
        }




        $s->Step3Completed = true;
        $s->Step3AdminId = $req->AdminId;

        $saver = $s->save();
        if($saver){
            $resMessage ="Stage 3 completed successfully";
            $adminMessage = "Completed Stage 3 successfully".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Step 3"], 400);
        }






    }


    public function GetStep4Members(Request $req){
        $s = OnBoarding::where("Step4Completed",false)
        ->get();
        return $s;
    }

    public function UserManual(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->UserManual = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="User Manual with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed User Manual with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete User Manual"], 400);
        }






    }

    public function MOUSignature(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }

        $s->MOUSignature = true;

        $saver = $s->save();
        if($saver){
            $resMessage ="MOU Signature with ".$s->CustomerName." completed successfully";
            $adminMessage = "Completed MOU Signature with ".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete MOU Signature"], 400);
        }






    }

    public function CompleteStep4(Request $req){

        $s = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$s){
            return response()->json(["message"=>"Customer not found"],400);
        }


        if($s->UserManual != true){
            return response()->json(["message"=>"UserManual not completed"],400);
        }

        if($s->MOUSignature != true){
            return response()->json(["message"=>"MOU Signature not completed"],400);
        }

        if($s->SoftwareUpload != true){
            return response()->json(["message"=>"Software Upload not completed"],400);
        }

        if($s->ThirdPartyServices != true){
            return response()->json(["message"=>"Third Party Services not completed"],400);
        }

        if($s->Testing != true){
            return response()->json(["message"=>"Testing not completed"],400);
        }

        if($s->MOUAgreement != true){
            return response()->json(["message"=>"MOU Agreement not completed"],400);
        }

        if($s->DomainAndHosting != true){
            return response()->json(["message"=>"Domain And Hosting not completed"],400);
        }

        if($s->PaymentCompleted != true){
            return response()->json(["message"=>"Payment not completed"],400);
        }

        if($s->FirstMeeting != true){
            return response()->json(["message"=>"First meeting not completed"],400);
        }







        $s->Step4Completed = true;
        $s->Step4AdminId = $req->AdminId;

        $saver = $s->save();
        if($saver){
            $resMessage ="Stage 4 completed successfully";
            $adminMessage = "Completed Stage 4 successfully".$s->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message"=>$resMessage],200);
        }
        else{
            return response()->json(["message" => "Failed to complete Step 4"], 400);
        }






    }

    public function GetFinalMembers(Request $req){
        $s = OnBoarding::where("Step4Completed",true)
        ->get();
        return $s;
    }


    public function DeBoard(Request $req){

        $c = OnBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$c){
            return response()->json(["message"=>"Sales not found"],400);
        }



        $s = new DeBoarding();
        $s->Created_By_Id =  $c->Created_By_Id;
        $s->Created_By_Name = $c->Created_By_Name;
        $s->TransactionId =   $c->TransactionId;
        $s->ProductId = $c->ProductId;
        $s->ProductName = $c->ProductName;
        $s->CustomerId = $c->CustomerId;
        $s->CustomerName = $c->CustomerName;
        $s->CustomerEmail = $c->CustomerEmail;
        $s->PricingType = $c->PricingType;
        $s->Amount = $c->Amount;
        $s->PaymentReference = $c->PaymentReference;


        $saver = $s->save();

        if($saver){
            $c->delete();
            $resMessage ="Deboarding Initialized for  ".$c->CustomerName.",  ";
            $adminMessage = "Completed Deboarding initialization for ".$c->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to initialize deboarding"], 400);
        }



    }


    public function GetAllDeBoard(Request $req){
        $s = DeBoarding::orderBy("created_at","desc")->get();
        return $s;
    }

    public function OnBoardFromDeBoard(Request $req){

        $c = DeBoarding::where("TransactionId",$req->TransactionId)->first();
        if(!$c){
            return response()->json(["message"=>"Sales not found"],400);
        }



        $s = new OnBoarding();
        $s->Created_By_Id =  $c->Created_By_Id;
        $s->Created_By_Name = $c->Created_By_Name;
        $s->TransactionId =   $c->TransactionId;
        $s->ProductId = $c->ProductId;
        $s->ProductName = $c->ProductName;
        $s->CustomerId = $c->CustomerId;
        $s->CustomerName = $c->CustomerName;
        $s->CustomerEmail = $c->CustomerEmail;
        $s->PricingType = $c->PricingType;
        $s->Amount = $c->Amount;
        $s->PaymentReference = $c->PaymentReference;


        $saver = $s->save();

        if($saver){
            $c->delete();
            $resMessage ="Onboarding Initialized for  ".$c->CustomerName.",  ";
            $adminMessage = "Completed Onboarding initialization for ".$c->CustomerName;
            $this->audit->Auditor($req->AdminId, $adminMessage);

            return response()->json(["message" => $resMessage], 200);


        }else{
            return response()->json(["message" => "Failed to initialize Onboarding"], 400);
        }



    }










}
