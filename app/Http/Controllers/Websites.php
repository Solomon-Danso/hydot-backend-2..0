<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hero;
use App\Http\Controllers\AuditTrialController;

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
    

   function ViewHero(){
    return Hero::get();
   }







}


