<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Partner;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use Illuminate\Support\Facades\Config;
use App\Mail\Partners_at_Hydottech;

class PartnerController extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }


public function Test(){
    return "Test is Good";
}








function CreatePartner(Request $req){

        $user = Partner::where("Email",$req->Email)->first();
        if($user){
            return response()->json(["message"=>$req->Email." already exist"],400);
        }

    $s = new Partner();

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("","public");
    }



        $s->UserId = $this->audit->IdGenerator();


    if($req->filled("Continent")){
        $s->Continent = $req->Continent;
    }

    if($req->filled("Country")){
        $s->Country = $req->Country;
    }

    if($req->filled("Name")){
        $s->Name = $req->Name;
    }

    if($req->filled("Location")){
        $s->Location = $req->Location;
    }

    if($req->filled("Phone")){
        $s->Phone = $req->Phone;
    }

    if($req->filled("Email")){
        $s->Email = $req->Email;
    }

    $rawPassword =  $this->audit->RandomIdGenerator();

    $s->Password = bcrypt($rawPassword);

    $s->Role = "Partner";

  //  $s->IsBlocked = true;

    $saver = $s->save();
    if($saver){

     $message = "Your registration was successful. Please check your email for further instructions.";
        $message2 = $s->Name."  is added as a partner";
        //$this->audit->Auditor($req->AdminId, $message);

        try {
            Mail::to($s->Email)->send(new Partners_at_Hydottech($s, $rawPassword));
            return response()->json(["message" => $message], 200);
        } catch (\Exception $e) {

            return response()->json(["message" => "Email Failed"], 400);
        }


    }else{
        return response()->json(["message" => "Could not add Admin"], 400);
    }




   }

function UpdatePartner(Request $req){

    $s = Partner::where("UserId", $req->header('UserId'))->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("","public");
    }






    if($req->filled("Continent")){
        $s->Continent = $req->Continent;
    }

    if($req->filled("Country")){
        $s->Country = $req->Country;
    }

    if($req->filled("Name")){
        $s->Name = $req->Name;
    }

    if($req->filled("Location")){
        $s->Location = $req->Location;
    }

    if($req->filled("Phone")){
        $s->Phone = $req->Phone;
    }

    if($req->filled("Email")){
        $s->Email = $req->Email;
    }

    if($req->filled("Password")){
        $s->Password = bcrypt($req->Password);
    }







    $saver = $s->save();
    if($saver){

        $message = $s->Name."  details was updated";
        $this->audit->Auditor($req->AdminId, $message);


        return response()->json(["message" => "User Information Updated "], 200);

    }else{
        return response()->json(["message" => "Could not update Admin"], 400);
    }




   }


   function ViewSinglePartner(Request $req){
    $s = Partner::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $message = $s->Name."  details was viewed";
    $this->audit->Auditor($req->AdminId, $message);


   return $s;
   }


function BlockPartner(Request $req){
    $s = Partner::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $s->IsBlocked=true;
    $s->LoginAttempt=3;



    $saver = $s->save();
    if($saver){
        $message = $s->Name."  has been blocked";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$message],200);
    }
    else{
        return response()->json(["message"=>"Failed to block ".$s->Name],400);
    }

   }

   function UnBlockPartner(Request $req){
    $s = Partner::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $s->IsBlocked=false;
    $s->LoginAttempt=0;



    $saver = $s->save();
    if($saver){
        $message = $s->Name."  has been Unblocked";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$message],200);
    }
    else{
        return response()->json(["message"=>"Failed to Unblock ".$s->Name],400);
    }

   }

   function UnLocker(Request $req){
    $s = Partner::where("Email", $req->Email)->where('Role', 'SuperAdmin')->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $s->IsBlocked=false;
    $s->LoginAttempt=0;
    $s->Token = null;
    $s->TokenExpire = null;



    $saver = $s->save();
    if($saver){
        $message = $s->Name."  has been Unblocked";
        $this->audit->Auditor($req->AdminId, $message);
        return response()->json(["message"=>$message],200);
    }
    else{
        return response()->json(["message"=>"Failed to Unblock ".$s->Name],400);
    }

   }




function EditPartner(Request $req){

    $sec = Partner::where("Role", "SuperAdmin")->first();

    $s = Partner::where("UserId", $req->PartnerId)->first();
    if($s==null){
        return response()->json(["message"=>"Wrong Administrator Id"],400);

    }

    if($sec->Email === $req->Email){
        return response()->json(["message"=>"You cannot assign a Super Administrator email to a regular administrator. "],400);
    }

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("","public");
    }






    if($req->filled("Continent")){
        $s->Continent = $req->Continent;
    }

    if($req->filled("Country")){
        $s->Country = $req->Country;
    }

    if($req->filled("Name")){
        $s->Name = $req->Name;
    }

    if($req->filled("Location")){
        $s->Location = $req->Location;
    }

    if($req->filled("Phone")){
        $s->Phone = $req->Phone;
    }

    if($req->filled("Email")){
        $s->Email = $req->Email;
    }

    if($req->filled("Password")){
        $s->Password = bcrypt($req->Password);
    }







    $saver = $s->save();
    if($saver){

        $message = $s->Name."  details was updated";
        $this->audit->Auditor($req->AdminId, $message);


        return response()->json(["message" => "User Information Updated "], 200);

    }else{
        return response()->json(["message" => "Could not update Admin"], 400);
    }




   }


   function ViewAllPartner(Request $req) {
    $s = Partner::where('Role', '!=', 'SuperAdmin')->get();

    if ($s->isEmpty()) {
        return response()->json(['message' => 'Admin not found'], 400);
    }


    $this->audit->Auditor($req->AdminId, "Viewed All Administrators");


    return response()->json($s);
}




function DeletePartner(Request $req){
    $s = Partner::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $saver = $s->delete();
    if($saver){

        $message = $s->Name."  details was deleted";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Deleted Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Deletion Failed"],400);
    }


}











}
