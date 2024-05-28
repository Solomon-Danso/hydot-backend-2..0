<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuditTrialController;
use Illuminate\Support\Facades\Config;
use App\Mail\Registration;

class AdminUserController extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }


public function Test(){
    return "Test is Good";
}


    public function SetUpCreateAdmin(Request $req)
    {
        if (Config::get('app.setup_completed')) {
            return response()->json(["message" => "Admin setup has already been completed"], 400);
        }
        
        if (AdminUser::count() > 0) {
            return response()->json([
                "message" => "Warning: An Admin account already exists. This action is not permitted and could have serious consequences. Do not attempt this again."
            ], 400);
        }

        if (Config::get('app.setup_completed')) {
            return response()->json(["message" => "Admin setup has already been completed"], 400);
        }

        $s = new AdminUser();

        if ($req->hasFile("Picture")) {
            $s->Picture = $req->file("Picture")->store("", "public");
        }

        $s->UserId = $this->IdGenerator();

        $fields = ['Continent', 'Country', 'Name', 'Location', 'Phone', 'Email'];
        foreach ($fields as $field) {
            if ($req->filled($field)) {
                $s->$field = $req->$field;
            }
        }

        $rawPassword = $this->IdGenerator();
        $s->Password = bcrypt($rawPassword);
        $s->Role = AdminUser::count() < 1 ? "SuperAdmin" : "Admin";

        if ($s->save()) {

            try {
                Mail::to($s->Email)->send(new Registration($s, $rawPassword));
                 // Set the flag to true after successful setup
                 Config::set('app.setup_completed', true);
                 // Update .env file (optional but recommended)
                 $this->updateEnv(['ADMIN_SETUP_COMPLETED' => 'true']);

                return response()->json(["message" => "Success"], 200);
            } catch (\Exception $e) {
                return response()->json(["message" => "Email Failed"], 400);
            }
        } else {
            return response()->json(["message" => "Could not add Admin"], 400);
        }
}


    protected function updateEnv($data = array())
    {
        if (count($data) > 0) {
            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');
            // Split string on every " " and write into array
            $env = explode("\n", $env);
            // Loop through given data
            foreach ((array)$data as $key => $value) {
                // Loop through .env-data
                foreach ($env as $env_key => $env_value) {
                    // Turn the value into an array and stop after the first split
                    $entry = explode("=", $env_value, 2);
                    // Check, if new key fits the actual .env-key
                    if ($entry[0] == $key) {
                        // If yes, overwrite it
                        $env[$env_key] = $key . "=" . $value;
                    } else {
                        // If not, keep it
                        $env[$env_key] = $env_value;
                    }
                }
            }
            // Turn the array back to a string
            $env = implode("\n", $env);
            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);
        }
    }







    function CreateAdmin(Request $req){

    $s = new AdminUser();

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("","public");
    }

    $s->save();


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

    $counter  = AdminUser::Count();

    if ($counter<1){
        $s->Role = "SuperAdmin";
    }
    else{
        $s->Role = "Admin";
    }

    $saver = $s->save();
    if($saver){


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

   function UpdateAdmin(Request $req){

    $s = AdminUser::where("UserId", $req->UserId)->first();

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

    $rawPassword = $this.IdGenerator();

    $s->Password = bcrypt($rawPassword);

    $counter  = AdminUser::Count();

    if ($counter<1){
        $s->Role = "SuperAdmin";
    }
    else{
        $s->Role = "Admin";
    }

    $saver = $s->save();
    if($saver){


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

   function ViewSingleAdmin(Request $req){
    $s = AdminUser::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

   return $s;
   }

   function ViewAllAdmin(Request $req) {
    $s = AdminUser::where('Role', '!=', 'SuperAdmin')->get();

    if ($s->isEmpty()) {
        return response()->json(['message' => 'Admin not found'], 400);
    }

    return response()->json($s);
}





function DeleteAdmin(Request $req){
    $s = AdminUser::where("UserId", $req->UserId)->first();

    if($s==null){
        return response()->json(["message"=>"Admin not found"],400);
    }

    $saver = $s->delete();
    if($saver){
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
