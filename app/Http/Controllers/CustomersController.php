<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Customers;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use Illuminate\Support\Facades\Config;
use App\Mail\CustomersRegistration;

class CustomersController extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }




    function CreateCustomers(Request $req){

        $user = Customers::where("Email",$req->Email)->first();
        if($user){
            return response()->json(["message"=>$req->Email." already exist"],400);
        }

    $s = new Customers();

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("","public");
    }



        $s->UserId = $this->IdGenerator();


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



    $saver = $s->save();
    if($saver){

        $message = $s->Name."  was added as an Customers";
        $message2 = $s->Name."  is added as an Customers";
        $this->audit->Auditor($req->AdminId, $message);

        try {
            Mail::to($s->Email)->send(new CustomersRegistration($s));
            return response()->json(["message" => $message2], 200);
        } catch (\Exception $e) {

            return response()->json(["message" => $e->getMessage() ], 400);
        }


    }else{
        return response()->json(["message" => "Could not add Admin"], 400);
    }



   }

function UpdateCustomers(Request $req){

    $s = Customers::where("UserId", $req->CustomersUserId)->first();

    if($s==null){
        return response()->json(["message"=>"Customers not found"],400);
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
        return response()->json(["message" => "Could not update Customers"], 400);
    }




   }


   function ViewSingleCustomers(Request $req){
    $s = Customers::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Customers not found"],400);
    }

    $message = $s->Name."  details was viewed";
    $this->audit->Auditor($req->AdminId, $message);


   return $s;
   }

   function ViewAllCustomers(Request $req) {
    $s = Customers::get();

    if ($s->isEmpty()) {
        return response()->json(['message' => 'Customers not found'], 400);
    }


    $this->audit->Auditor($req->AdminId, "Viewed All Employeeistrators");


    return response()->json($s);
}






function DeleteCustomers(Request $req){
    $s = Customers::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Customers not found"],400);
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








function IdGenerator(): string {
    $randomID = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    return $randomID;
}


}
