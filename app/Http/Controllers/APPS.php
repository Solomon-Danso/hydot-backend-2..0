<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheduler;
use App\Http\Controllers\AuditTrialController;
use Carbon\Carbon;

class APPS extends Controller
{
    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }

    function CreateSchedular(Request $req) {
        $s = new Scheduler();

        // List of fields to be filled
        $fields = ['Description', 'StartTime', 'EndTime', 'ScheduleId', 'StartTimezone', 'EndTimezone', 'Subject', 'Location', 'IsAllDay', 'RecurrenceRule'];

        foreach ($fields as $field) {
            if ($req->filled($field)) {
                // Convert StartTime and EndTime to Carbon instances if they are present
                if ($field == 'StartTime' || $field == 'EndTime') {
                    // Remove the redundant timezone part
                    $dateString = $req->$field;
                    // Example date string: "Wed Jun 05 2024 20:30:00 GMT+0000 (Greenwich Mean Time)"
                    // We only need: "Wed Jun 05 2024 20:30:00 GMT+0000"
                    $dateString = substr($dateString, 0, strpos($dateString, '(') - 1);
                    $s->$field = Carbon::parse($dateString);
                } else {
                    $s->$field = $req->$field;
                }
            }
        }

        $saver = $s->save();

        if ($saver) {
            $message = $s->Subject . " was scheduled";
            // Assuming $this->audit->Auditor is a valid method for logging the action
            $this->audit->Auditor($req->AdminId, $message);
            return response()->json(["message" => $s->Subject . " has been scheduled"], 200);
        } else {
            return response()->json(["message" => "Failed to Schedule"], 400);
        }
    }

function UpdateSchedular(Request $req){
    $s =  Scheduler::where("id",$req->Id)->first();
    if($s==null){
        return response()->json(["message"=>"Schedule not found"],400);
    }

    $fields = ['Description', 'StartTime', 'EndTime', 'ScheduleId', 'StartTimezone', 'EndTimezone', 'Subject', 'Location', 'IsAllDay', 'RecurrenceRule'];

        foreach ($fields as $field) {
            if ($req->filled($field)) {
                // Convert StartTime and EndTime to Carbon instances if they are present
                if ($field == 'StartTime' || $field == 'EndTime') {
                    // Remove the redundant timezone part
                    $dateString = $req->$field;
                    // Example date string: "Wed Jun 05 2024 20:30:00 GMT+0000 (Greenwich Mean Time)"
                    // We only need: "Wed Jun 05 2024 20:30:00 GMT+0000"
                    $dateString = substr($dateString, 0, strpos($dateString, '(') - 1);
                    $s->$field = Carbon::parse($dateString);
                } else {
                    $s->$field = $req->$field;
                }
            }
        }


$saver = $s->save();
if($saver){
    $message = $s->Subject." schedule was updated";
    $this->audit->Auditor($req->AdminId, $message);
    return response()->json(["message"=>$s->Subject." schedule is updated"],200);
}
else{
    return response()->json(["message"=>"Failed to Update Schedule"],400);
}


}

function DeleteSchedule(Request $req){
    $s =  Scheduler::where("id",$req->Id)->first();
    if($s==null){
        return response()->json(["message"=>"Schedule not found"],400);
    }

    $saver = $s->delete();


    if($saver){
    $message = $s->Subject." schedule was deleted";
    $this->audit->Auditor($req->AdminId, $message);
    return response()->json(["message"=>$s->Subject." schedule is deleted"],200);
    }
    else{
    return response()->json(["message"=>"Failed to delete Schedule"],400);
    }


}

function GetSchedule(){
    return Scheduler::get();
}









}
