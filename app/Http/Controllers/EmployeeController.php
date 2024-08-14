<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use Illuminate\Support\Facades\Config;
use App\Mail\EmployeeRegistration;

class EmployeeController extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }




    function CreateEmployee(Request $req){

        $user = Employee::where("Email",$req->Email)->first();
        if($user){
            return response()->json(["message"=>$req->Email." already exist"],400);
        }

    $s = new Employee();

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


    $s->Role = "Employee";

    $saver = $s->save();
    if($saver){

        $message = $s->Name."  was added as an employee";
        $message2 = $s->Name."  is added as an employee";
        $this->audit->Auditor($req->AdminId, $message);

        try {
            Mail::to($s->Email)->send(new EmployeeRegistration($s));
            return response()->json(["message" => $message2], 200);
        } catch (\Exception $e) {

            return response()->json(["message" => $e->getMessage() ], 400);
        }


    }else{
        return response()->json(["message" => "Could not add Admin"], 400);
    }



   }

function UpdateEmployee(Request $req){

    $s = Employee::where("UserId", $req->EmployeeUserId)->first();

    if($s==null){
        return response()->json(["message"=>"Employee not found"],400);
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
        return response()->json(["message" => "Could not update Employee"], 400);
    }




   }


   function ViewSingleEmployee(Request $req){
    $s = Employee::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Employee not found"],400);
    }

    $message = $s->Name."  details was viewed";
    $this->audit->Auditor($req->AdminId, $message);


   return $s;
   }


function EditEmployee(Request $req){

    $sec = Employee::where("Role", "SuperEmployee")->first();

    $s = Employee::where("UserId", $req->AdminId)->first();
    if($s==null){
        return response()->json(["message"=>"Wrong Employeeistrator Id"],400);

    }

    if($sec->Email === $req->Email){
        return response()->json(["message"=>"You cannot assign a Super Employeeistrator email to a regular Employeeistrator. "],400);
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
        return response()->json(["message" => "Could not update Employee"], 400);
    }




   }


   function ViewAllEmployee(Request $req) {
    $s = Employee::where('Role', '!=', 'SuperEmployee')->get();

    if ($s->isEmpty()) {
        return response()->json(['message' => 'Employee not found'], 400);
    }


    $this->audit->Auditor($req->AdminId, "Viewed All Employeeistrators");


    return response()->json($s);
}




function DeleteEmployee(Request $req){
    $s = Employee::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Employee not found"],400);
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
