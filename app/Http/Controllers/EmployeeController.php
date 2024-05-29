<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{



    function CreateEmployee(Request $req){

        $s = new EmployeeUser();
    
        if($req->hasFile("Picture")){
            $s->Picture = $req->file("Picture")->store("","public");
        }
    
    
    
            $s->UserId = $this.IdGenerator();
    
    
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
    
        $rawPassword = $this.IdGenerator();
    
        $s->Password = bcrypt($rawPassword);
    
        $s->Role = "SuperAdmin";
    
        $saver = $s->save();
        if($saver){
    
            $message = $s->Name."  was added as an administrator";
            $this->audit->Auditor($req->AdminId, $message);
    
            try {
                Mail::to($s->Email)->send(new Registration($s, $rawPassword));
                return response()->json(["message" => "Success"], 200);
            } catch (\Exception $e) {
    
                return response()->json(["message" => "Email Failed"], 400);
            }
    
    
        }else{
            return response()->json(["message" => "Could not add Admin"], 400);
        }
    
    
    
    
       }
    
    function UpdateEmployee(Request $req){
    
        $s = EmployeeUser::where("UserId", $req->UserId)->first();
    
        if($s==null){
            return response()->json(["message"=>"Admin not found"],400);
        }
    
        if($req->hasFile("Picture")){
            $s->Picture = $req->file("Picture")->store("","public");
        }
    
    
    
            $s->UserId = $this.IdGenerator();
    
    
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
    
    
       function ViewSingleEmployee(Request $req){
        $s = EmployeeUser::where("UserId", $req->UserId)->first();
    
        if($s==null){
            return response()->json(["message"=>"Admin not found"],400);
        }
    
        $message = $s->Name."  details was viewed";
        $this->audit->Auditor($req->AdminId, $message);
    
    
       return $s;
       }
    
       function ViewAllEmployee(Request $req) {
        $s = EmployeeUser::where('Role', '!=', 'SuperAdmin')->get();
    
        if ($s->isEmpty()) {
            return response()->json(['message' => 'Admin not found'], 400);
        }
    
        
        $this->audit->Auditor($req->AdminId, "Viewed All Administrators");
    
    
        return response()->json($s);
    }
    
    
    
    
    
    function DeleteEmployee(Request $req){
        $s = EmployeeUser::where("UserId", $req->UserId)->first();
    
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
    
    
    
    function IdGenerator(): string {
        $randomID = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        return $randomID;
    }
    




}
