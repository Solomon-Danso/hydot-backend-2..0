<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hero;
use App\Http\Controllers\AuditTrialController;
use App\Models\WhatWeDo;
use App\Models\OurDifferences;
use App\Models\OurProcess;
use App\Models\OurPortfolioHeader;
use App\Models\OurPortfolioProjects;
use App\Models\OurClientHeader;
use App\Models\OurClients;
use App\Models\Testimonials;

class Websites extends Controller
{

    protected $audit;


    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }


    function CreateHero(Request $req) {
        $s =  Hero::firstOrNew();
        $fields = ['Picture1', 'Picture2', 'Picture3', 'Picture4', 'Picture5', 'Picture6'];

        foreach ($fields as $field) {
            if ($req->hasFile($field)) {
                $s->$field = $req->file($field)->store("", "public");
            }
        }

        $sections = ['Section1', 'Section2', 'Section3', 'Section4']; // Added missing semicolon here

        foreach ($sections as $sec) {
            if ($req->filled($sec)) {
                $s->$sec = $req->$sec;
            }
        }

        $saver = $s->save();
        if ($saver) {
            $message = "Hero Was Created";
            $this->audit->Auditor($req->AdminId, $message);

            return response()->json(["message" => "Hero Created Successfully"], 200);
        } else {
            return response()->json(["message" => "An error occurred creating hero"], 400);
        }
    }


    public function ViewHero()
    {
        // Fetch the current data from the database
        $heroes = Hero::all();

        // Assuming you want to map the first Hero item returned, adjust as needed
        $currentData = $heroes->first();

        // Map the current data to the desired format
        $mappedData = [
            [
                'src' => "{$currentData->Picture1}",
                'bg' => "#0be4d1",
                'delay' => 0.1,
            ],
            [
                'src' => "{$currentData->Picture2}",
                'bg' => "#fde490",
                'delay' => 0.3,
            ],
            [
                'src' => "{$currentData->Picture3}",
                'bg' => "#00c9f7",
                'delay' => 0.2,
            ],
            [
                'src' => "{$currentData->Picture4}",
                'bg' => "#83cfdf",
                'delay' => 0.2,
            ],
            [
                'src' => "{$currentData->Picture5}",
                'bg' => "#fe8856",
                'delay' => 0.3,
            ],
            [
                'src' => "{$currentData->Picture6}",
                'bg' => "#0be4d1",
                'delay' => 0.25,
            ],
        ];

        $final = [
            'mapped'=>$mappedData,
            'section'=>$currentData
        ];

        return response()->json($final);
    }

    function ViewAdminHero(){
        $heroes = Hero::all();

        // Assuming you want to map the first Hero item returned, adjust as needed
        $currentData = $heroes->first();

        return response()->json([$currentData]);
    }





   function CreateWhatWeDo(Request $req){
    $s = WhatWeDo::firstOrNew();

    $fields = [
        'Main_Title','Secondary_Title',
        'Left_Main_Title','Left_Secondary_Title','Left_Text1','Left_Text2','Left_Text3',
        'Right_Main_Title','Right_Secondary_Title','Right_Text1','Right_Text2','Right_Text3',
        'Middle_Main_Title','Middle_Secondary_Title','Middle_Text1','Middle_Text2'
    ];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    };

    $saver = $s->save();

    if($saver){
        $message = "What We Do Section Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"What We Do Section Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"What We Do Section Failed to update"],400);

    }




   }

   function ViewWhatWeDo(){
    $heroes = WhatWeDo::all();
    $currentData = $heroes->first();

    $mappedData = [
        [
            'icon'=> "/features/Frame-0.png",
            'title' => "{$currentData->Left_Text1}",
        ],
        [
            'icon'=> "/features/Frame-1.png",
            'title' => "{$currentData->Left_Text2}",
        ],
        [
            'icon'=> "/features/Frame-2.png",
            'title' => "{$currentData->Left_Text3}",
        ],
        [
            'icon'=> "/features/Frame-3.png",
            'title' => "{$currentData->Right_Text1}",
        ],
        [
            'icon'=> "/features/Frame-4.png",
            'title' => "{$currentData->Right_Text2}",
        ],
        [
            'icon'=> "/features/Frame-5.png",
            'title' => "{$currentData->Right_Text3}",
        ],
    ];

    $final = [
        'mapped'=>$mappedData,
        'section'=>$currentData
    ];

    return response()->json($final);

}

function ViewAdminWhatWeDo(){
    $heroes = WhatWeDo::all();

    // Assuming you want to map the first Hero item returned, adjust as needed
    $currentData = $heroes->first();

    return response()->json([$currentData]);
}




   function CreateOurDifferences(Request $req){
    $s = OurDifferences::firstOrNew();

    $fields = [
        'Main_Title','Secondary_Title',
        'Left_Main_Title','Left_Description',
        'Right_Main_Title','Right_Description',
        'Middle_Main_Title','Middle_Description',

    ];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    };

    $saver = $s->save();

    if($saver){
        $message = "Our Differences Section Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Differences Section Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Differences Section Failed to update"],400);

    }




   }



   function ViewOurDifferences(){
    $heroes = OurDifferences::all();
    $currentData = $heroes->first();

    $mappedData = [
        [
            'icon'=> "/OurDiff/Frame-0.png",
            'title' => "{$currentData->Left_Main_Title}",
            'des' => "{$currentData->Left_Description}",
        ],
        [
            'icon'=> "/OurDiff/Frame-1.png",
            'title' => "{$currentData->Right_Main_Title}",
            'des' => "{$currentData->Right_Description}",
        ],
        [
            'icon'=> "/OurDiff/Frame-2.png",
            'title' => "{$currentData->Middle_Main_Title}",
            'des' => "{$currentData->Middle_Description}",
        ],

    ];

    $final = [
        'mapped'=>$mappedData,
        'section'=>$currentData
    ];

    return response()->json($final);

}

function ViewAdminOurDifferences(){
    $heroes = OurDifferences::all();

    // Assuming you want to map the first Hero item returned, adjust as needed
    $currentData = $heroes->first();

    return response()->json([$currentData]);
}


   function CreateOurProcess(Request $req){
    $s = OurProcess::firstOrNew();

    $fields = [
        'Main_Title','Secondary_Title',
        'Left_Main_Title','Left_Description',
        'Right_Main_Title','Right_Description',
        'Middle_Main_Title','Middle_Description',

    ];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    };

    $saver = $s->save();

    if($saver){
        $message = "Our Process Section Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Process Section Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Process Section Failed to update"],400);

    }




   }


   function ViewOurProcess(){
    $heroes = OurProcess::all();
    $currentData = $heroes->first();

    $mappedData = [
        [
            'icon'=> "/howItWorks/Frame-0.png",
            'title' => "{$currentData->Left_Main_Title}",
            'des' => "{$currentData->Left_Description}",
        ],
        [
            'icon'=> "/howItWorks/Frame-1.png",
            'title' => "{$currentData->Right_Main_Title}",
            'des' => "{$currentData->Right_Description}",
        ],
        [
            'icon'=> "/howItWorks/Frame-2.png",
            'title' => "{$currentData->Middle_Main_Title}",
            'des' => "{$currentData->Middle_Description}",
        ],

    ];

    $final = [
        'mapped'=>$mappedData,
        'section'=>$currentData
    ];

    return response()->json($final);

}

function ViewAdminOurProcess(){
    $heroes = OurProcess::all();

    // Assuming you want to map the first Hero item returned, adjust as needed
    $currentData = $heroes->first();

    return response()->json([$currentData]);
}


   function CreateOurPortfolioHeader(Request $req){
    $s = OurPortfolioHeader::firstOrNew();

    $fields = [
        'Main_Title','Secondary_Title',
    ];

    foreach($fields as $field){
        if($req->filled($field)){
            $s->$field = $req->$field;
        }
    };

    $saver = $s->save();

    if($saver){
        $message = "Portfolio Section Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Portfolio Header Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Portfolio Header Failed to update"],400);

    }




   }

   function ViewOurPortfolioHeader(){
    $c = OurPortfolioHeader::first();
    return $c;
   }

   function ViewAdminOurPortfolioHeader(){
    $heroes = OurPortfolioHeader::all();

    // Assuming you want to map the first Hero item returned, adjust as needed
    $currentData = $heroes->first();

    return response()->json([$currentData]);
}


   function CreateOurPortfolioProjects(Request $req){

    $user = OurPortfolioProjects::where("ProjectName",$req->ProjectName)->first();
    if($user){
        return response()->json(["message"=>$req->ProjectName." already exist"],400);
    }

    $s = new OurPortfolioProjects();

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("", "public");
    }

    if($req->filled("Link")){
        $s->Link = $req->Link;
    }

    if($req->filled("ProjectName")){
        $s->ProjectName = $req->ProjectName;
    }

    $s->ProjectId = $this->IdGenerator();




    $saver = $s->save();

    if($saver){
        $message = "Portfolio Project Created";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Portfolio Project Created Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Portfolio Project Creation Failed"],400);

    }




   }

   function UpdateOurPortfolioProjects(Request $req){


    $s =  OurPortfolioProjects::where('ProjectId',$req->ProjectId)->first();
    if($s==null){
        return response()->json(["message"=>"Projects does not exist"],400);
    }

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("", "public");
    }

    if($req->filled("Link")){
        $s->Link = $req->Link;
    }

    if($req->filled("ProjectName")){
        $s->ProjectName = $req->ProjectName;
    }



    $saver = $s->save();

    if($saver){
        $message = "Portfolio Project Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Portfolio Project Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Portfolio Project Update Failed"],400);

    }




   }

   function ViewOurPortfolioProjects(){
    return OurPortfolioProjects::get();
   }


   function DeleteOurPortfolioProjects(Request $req){

    $p = OurPortfolioProjects::where("ProjectId",$req->ProjectId)->first();
    if($p==null){
        return response()->json(["message"=>"Project does not exist"],400);
    }


    $saver = $p->delete();

    if($saver){
        $message = "Portfolio Project Deleted";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Portfolio Project Deleted Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Portfolio Project Deletion Failed"],400);

    }


   }



   function CreateOurClientsHeader(Request $req){
    $s = OurClientHeader::firstOrNew();

    if($req->hasFile("Picture")){
        $s->Picture = $req->file("Picture")->store("", "public");
    }

    if($req->filled("Main_Title")){
        $s->Main_Title = $req->Main_Title;
    }

    $saver = $s->save();

    if($saver){
        $message = "Our Clients Section Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Clients Header Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Clients Header Failed to update"],400);

    }




   }

   function ViewOurClientsHeader(){
    return OurClientHeader::first();
   }

   function CreateOurClientsProjects(Request $req){


    $s = new OurClients();


    if($req->filled("Category")){
        $s->Category = $req->Category;
    }

    if($req->filled("Description")){
        $s->Description = $req->Description;
    }


    $saver = $s->save();

    if($saver){
        $message = "Our Clients Created";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Clients Created Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Clients Creation Failed"],400);

    }




   }

   function UpdateOurClientsProjects(Request $req){


    $s =  OurClients::where('id',$req->Id)->first();
    if($s==null){
        return response()->json(["message"=>"Clients Project does not exist"],400);
    }


    if($req->filled("Category")){
        $s->Category = $req->Category;
    }

    if($req->filled("Description")){
        $s->Description = $req->Description;
    }


    $saver = $s->save();

    if($saver){
        $message = "Our Clients Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Clients Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Clients Update Failed"],400);

    }




   }



   function ViewOurClientsProjects(){
    return OurClients::get();
   }


   function DeleteOurClientsProjects(Request $req){

    $p = OurClients::where("id",$req->Id)->first();
    if($p==null){
        return response()->json(["message"=>"Project does not exist"],400);
    }


    $saver = $p->delete();

    if($saver){
        $message = "Our Clients Project Deleted";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Our Clients Project Deleted Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Our Clients Project Deletion Failed"],400);

    }




   }


   function CreateTestimonials(Request $req){


    $s = new Testimonials();


    if($req->filled("Message")){
        $s->Message = $req->Message;
    }

    if($req->filled("Fullname")){
        $s->Fullname = $req->Fullname;
    }

    if($req->filled("Position")){
        $s->Position = $req->Position;
    }


    $saver = $s->save();

    if($saver){
        $message = "Testimonials Created";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Testimonials Created Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Testimonials Creation Failed"],400);

    }




   }

   function UpdateTestimonials(Request $req){


    $s = Testimonials::where('id',$req->Id)->first();
    if($s==null){
        return response()->json(["message"=>"Testimonials does not exist"],400);
    }


    if($req->filled("Message")){
        $s->Message = $req->Message;
    }

    if($req->filled("Fullname")){
        $s->Fullname = $req->Fullname;
    }

    if($req->filled("Position")){
        $s->Position = $req->Position;
    }


    $saver = $s->save();

    if($saver){
        $message = "Testimonials Updated";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Testimonials Updated Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Testimonials Update Failed"],400);

    }




   }

   function ViewTestimonials(){
    // Fetch all testimonials from the database
    $testimonials = Testimonials::all();



    // Return the transformed data as JSON
    return  $testimonials;
}




   function DeleteTestimonials(Request $req){

    $p = Testimonials::where("id",$req->Id)->first();
    if($p==null){
        return response()->json(["message"=>"Testimonials does not exist"],400);
    }


    $saver = $p->delete();

    if($saver){
        $message = "Testimonials Deleted";
        $this->audit->Auditor($req->AdminId, $message);

        return response()->json(["message"=>"Testimonials Deleted Successfully"],200);
    }
    else{
        return response()->json(["message"=>"Testimonials Deletion Failed"],400);

    }




   }











   function IdGenerator(): string {
    $randomID = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    return $randomID;
}



}


