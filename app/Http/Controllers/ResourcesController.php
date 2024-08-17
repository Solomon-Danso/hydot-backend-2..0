<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuditTrialController;
use App\Models\Resources;
use App\Models\Partner;
use App\Models\AdminUser;
use App\Mail\Resource;
use Illuminate\Support\Facades\Mail;
use App\Models\BulkSender;

class ResourcesController extends Controller
{
    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }

    public function CreateResources(Request $req){

        $s = new Resources();
        $ResourceID = $this->audit->IdGeneratorLong();

        if ($req->Category=="Partners"){

            $partners = Partner::pluck('UserId');


         foreach($partners as $partner){

                $s->ResourceId = $ResourceID;
                $s->Category = "Partners";
                $s->UserId = $partner;


                if($req->hasFile("Resource")){
                    $s->Resource = $req->file("Resource")->store("","public");
                }


                if($req->filled("ResourceType")){
                    $s->ResourceType = $req->ResourceType;
                }

                if($req->filled("Title")){
                    $s->Title = $req->Title;
                }

               $saver= $s->save();
               if($saver){
                $worked = true;
               }
               else{
                $worked = false;
                return response()->json(["message"=> "All worked except the partner with ID ".$partner],200);

               }

            }

            if ($worked){
                $resMessage ="A ".$s->ResourceType." resource was added successfully";
                $adminMessage = "Added ".$s->ResourceType." resource to partners";
                $this->audit->Auditor($req->AdminId, $adminMessage);
                return response()->json(["message"=> $resMessage],200);
            }
            else{
                return response()->json(["message"=> "Failed to send resources for partner"],200);
            }

        }

        if ($req->Category=="Admin"){

            $partners = AdminUser::pluck('UserId');
            $worked = false;

         foreach($partners as $partner){

                $s->ResourceId = $ResourceID;
                $s->Category = "Admin";
                $s->UserId = $partner;


                if($req->hasFile("Resource")){
                    $s->Resource = $req->file("Resource")->store("","public");
                }


                if($req->filled("ResourceType")){
                    $s->ResourceType = $req->ResourceType;
                }

                if($req->filled("Title")){
                    $s->Title = $req->Title;
                }

               $saver= $s->save();
               if($saver){
                $worked = true;
               }
               else{
                $worked = false;
                return response()->json(["message"=> "All worked except the admin with ID ".$partner],200);

               }

            }

            if ($worked){
                $resMessage ="A ".$s->ResourceType." resource was added successfully";
                $adminMessage = "Added ".$s->ResourceType." resource to partners";
                $this->audit->Auditor($req->AdminId, $adminMessage);
                return response()->json(["message"=> $resMessage],200);
            }
            else{
                return response()->json(["message"=> "Failed to send resources for partner"],200);
            }

        }

        if ($req->Category=="Customer"){

            $s->ResourceId = $ResourceID;
            $s->Category = "Customer";
            $s->UserId =  $req->Email;


            $attachmentName = null;
            if ($req->hasFile("Resource")) {
                $attachmentName = $req->file("Resource")->store("", "public");
            }


            if($req->filled("ResourceType")){
                $s->ResourceType = $req->ResourceType;
            }

            if($req->filled("Title")){
                $s->Title = $req->Title;
            }
            if($req->filled("Name")){
                $s->Name = $req->Name;
            }
            if($req->filled("Email")){
                $s->Email = $req->Email;
            }

           $saver= $s->save();
           if($saver){
            try {
                Mail::to($s->Email)->send(new Resource($s->Name, $s->Title, $attachmentName));

                return response()->json(["message" => "Resource sent successfully"]);
            } catch (\Exception $e) {
                // Return the exception message
                return response()->json(['message' => 'Email request failed: ' . $e->getMessage()], 400);
            }
           }
           else{
            return response()->json(["message"=> "Failed to send resource to  ".$s->Name],200);

           }


        }


        if ($req->Category=="Bulk"){

            $partners = BulkSender::pluck('Email');

            $worked = false;
            foreach($partners as $partner){


                $s->ResourceId = $ResourceID;
                $s->Category = "Bulk";
                $s->UserId =  $partner;


                $attachmentName = null;
                if ($req->hasFile("Resource")) {
                    $attachmentName = $req->file("Resource")->store("", "public");
                }


                if($req->filled("ResourceType")){
                    $s->ResourceType = $req->ResourceType;
                }

                if($req->filled("Title")){
                    $s->Title = $req->Title;
                }
                if($req->filled("Name")){
                    $s->Name = $req->Name;
                }
                if($req->filled("Email")){
                    $s->Email = $req->Email;
                }

                $saver= $s->save();
            if($saver){

                try {
                    Mail::to($partner)->send(new Resource($s->Name, $s->Title, $attachmentName));
                    $worked = true;
                    $d = BulkSender::where("Email", $partner)->first();
                    $d->delete();
                // return response()->json(["message" => "Resource sent successfully"]);
                } catch (\Exception $e) {

                    return response()->json(['message' => 'Email request failed: ' . $e->getMessage()], 400);
                }
            }
            else{
                return response()->json(["message"=> "Failed to send resource to  ".$s->Name],200);

            }



            }

            if($worked){
                return response()->json(["message"=>"Resources sent successfully"],200);
            }
            else{
                return response()->json(["message"=>"Resources sending failed"],400);
            }


        }







    }

    public function GetResources(Request $req){
        $s = Resources::where("UserId", $req->UserId)->get();

        return $s;
    }

    public function BulkEmail(Request $req){

        $s = new BulkSender();
        if($req->filled("UserId")){
            $s->UserId = $req->UserId;
        }
        if($req->filled("Email")){
            $s->Email = $req->Email;
        }
        $c = BulkSender::count();


        $saver = $s->save();
        if($saver){
            $counter = $c+1;
            return response()->json(["message"=> $s->Email." added successfully new bulk counter is ".$counter],200);
        }
        else{
            return response()->json(["message"=>"Failed to add to bulk sender"],400);
        }


    }




}
