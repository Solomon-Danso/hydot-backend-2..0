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
use Illuminate\Support\Facades\DB;

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
                return response()->json(["message"=> "Failed to send resources for partner"],400);
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
                return response()->json(["message"=> "Failed to send resources for admin"],400);
            }

        }

        if ($req->Category=="Customer"){

            $s->ResourceId = $ResourceID;
            $s->Category = "Customer";
            $s->UserId =  $req->Email;


            $attachmentName = null;
            if ($req->hasFile("Resource")) {
                $attachmentName = $req->file("Resource")->store("", "public");
                $s->Resource = $attachmentName;
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
            return response()->json(["message"=> "Failed to send resource to  ".$s->Name],400);

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
                return response()->json(["message"=> "Failed to send resource to  ".$s->Name],400);

            }



            }

            if($worked){
                return response()->json(["message"=>"Resources sent successfully"],200);
            }
            else{
                return response()->json(["message"=>"Resources sending failed"],400);
            }


        }

        if ($req->Category=="Individual"){



                $s->ResourceId = $ResourceID;
                $s->Category = "Individual";

                if($req->filled("UserId")){
                    $s->UserId = $req->UserId;
                }

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
                $resMessage ="A ".$s->ResourceType." resource was added successfully";
                $adminMessage = "Added ".$s->ResourceType." resource to Individuals";
                $this->audit->Auditor($req->AdminId, $adminMessage);
                return response()->json(["message"=> $resMessage],200);
               }
               else{
                return response()->json(["message"=> "Failed to send resources for individual"],400);

               }

        }










    }

    public function GetAllResources(Request $req){
        $s = Resources::distinct('ResourceId')->orderBy('created_at',"desc")->get();
        return $s;
    }

    public function GetGeneralResources(Request $req){
        // Fetch distinct resources by category
        $a = Resources::distinct('ResourceId')
            ->where("Category", $req->Category)->get();

        // Fetch distinct resources by user ID
        $b = Resources::distinct('ResourceId')
            ->where("UserId", $req->UserId)->get();

        // Merge the two collections
        $s = $a->merge($b);

        // Sort the merged collection by 'created_at' in descending order
        $s = $s->sortByDesc('created_at')->values();

        return $s;
    }

    public function DownloadImage(Request $request)
    {
        $filename = $request->input('filename');
        $filePath = storage_path('app/public/' . $filename);

        if (file_exists($filePath)) {
            return response()->download($filePath, $filename);
        } else {
            return response()->json(['message' => 'File not found'], 404);
        }
    }




    public function DeleteResource(Request $req){
        // Retrieve resources with the specified ResourceId
        $resourceList = Resources::where("ResourceId", $req->ResourceId)->get();

        // Check if the collection is empty
        if(!$resourceList->isEmpty()){
            $worked = false;

            // Iterate through the resource list and delete each resource
            foreach($resourceList as $resource){
                $saver = $resource->delete();
                if($saver){
                    $worked = true;
                } else {
                    $worked = false;
                    // Return response indicating partial success
                    return response()->json(["message" => "All deleted except the resource ".$req->Title], 200);
                }
            }

            // Return success or failure message
            if ($worked){
                $resMessage = "All deleted successfully";
                $adminMessage = "Deleted a resource ";
                $this->audit->Auditor($req->AdminId, $adminMessage);
                return response()->json(["message" => $resMessage], 200);
            } else {
                return response()->json(["message" => "Failed to delete resource"], 400);
            }
        } else {
            // Return response indicating that no resources were found
            return response()->json(["message" => "No resource found"], 404);
        }
    }






    public function GetSpecificResources(Request $req){
        $s = Resources::where("UserId", $req->UserId)->get();
        return $s;
    }

    public function GetCategoryResources(Request $req){
        $s = Resources::where("Category", $req->Category)->get();
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


    public function DeleteOneBulkEmail(Request $req)
    {
        $s = BulkSender::where("id", $req->id)->first();

        if ($s) {
            $s->delete();
            return response()->json(["message" => $s->Email . " Deleted"], 200);
        } else {
            return response()->json(["message" => "Resource not found"], 404);
        }
    }


    public function GetBulkEmail(Request $req){
        $s = BulkSender::orderBy("created_at","desc")->get();
       return $s;
    }




}
