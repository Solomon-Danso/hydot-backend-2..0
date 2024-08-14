<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use Illuminate\Support\Facades\Mail;
use App\Mail\Authentication;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Security;
use App\Http\Controllers\AuditTrialController;

class PartnerAuthentication extends Controller
{

    protected $audit;

    public function __construct(AuditTrialController $auditTrialController)
    {
        $this->audit = $auditTrialController;

    }



public function PartnerLogIn(Request $req)
    {

        // Use your custom Authentication model to authenticate
        $user = Partner::where('Email', $req->Email)->first();

        if ($user && Hash::check($req->Password, $user->Password)) {

            if($user->IsBlocked==true){
                return response()->json(['message' => 'Your account is currently inactive. Please contact HydotTech administrators at [customers@hydottech.com] for further assistance.'], 500);
            }
            else{

                $user->Token =  $this->audit->IdGeneratorLong();
                $user->TokenExpire = Carbon::now()->addMinutes(10);

                $saver = $user->save();
                if ($saver) {
                    // Send email if the request is successful
                    try {
                        Mail::to($user->Email)->send(new Authentication( $user->Token));
                        return response()->json(['message' => $user->Email], 200);
                    } catch (\Exception $e) {

                        return response()->json(['message' => 'Email Request Failed'], 400);
                    }



                } else {
                    return response()->json(['message' => 'Could not save the Token'], 500);
                }


            }


        } else {
            if($user->IsBlocked==true){
                return response()->json(['message' => 'Your Account Has Been Blocked, Contact Site Administrator For Further Instruction '], 500);
            }
            $user->LoginAttempt += 1;

            if($user->LoginAttempt>2){
                $user->IsBlocked=true;
                $user->save();
            }
            $user->save();

            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }







public function PartnerForgetPasswordStep1(Request $req)
    {

        // Use your custom Authentication model to authenticate
        $user = Partner::where('Email', $req->Email)->first();

        if ($user) {

            if($user->IsBlocked==true){
                return response()->json(['message' => 'Your Account Has Been Blocked, Contact Site Administrator For Further Instruction '], 500);
            }
            else{

                $user->Token =  $this->audit->IdGeneratorLong();
                $user->TokenExpire = Carbon::now()->addMinutes(10);

                $saver = $user->save();
                if ($saver) {
                    // Send email if the request is successful
                    try {
                        Mail::to($user->Email)->send(new Authentication( $user->Token));
                        return response()->json(['message' => "A verification token has been sent to ".$user->Email], 200);
                    } catch (\Exception $e) {

                        return response()->json(['message' => 'Email Request Failed'], 400);
                    }



                } else {
                    return response()->json(['message' => 'Could not save the Token'], 500);
                }


            }


        } else {

            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }






function PartnerForgetPasswordStep2(Request $req){
        $user = Partner::where('Email', $req->Email)->first();

        if ($user == null) {
            return response()->json(["message" => "User does not exist"], 400);
        }

        if ($user->Token === $req->token && Carbon::now() <= $user->TokenExpire) {
            // Invalidate the token and update the user attributes
            $user->Token = null;
            $user->TokenExpire = null;
            $user->LoginAttempt = 0;
            $user->IsBlocked = false;
            $user->Password = bcrypt($req->Password);

            $this-> Securities($user->Email);

            // Save the user
            $user->save();


            // Return the response
            return response()->json(["message" => "Password Updated Successfully"], 200);
        } else if (Carbon::now() > $user->TokenExpire) {
            return response()->json(["message" => "Your Token Has Expired"], 400);
        } else {
            return response()->json(["message" => "Invalid Token"], 400);
        }
    }











function PartnerVerifyToken(Request $req){
    $user = Partner::where('Email', $req->Email)->first();

    if ($user == null) {
        return response()->json(["message" => "User does not exist"], 400);
    }

    if ($user->Token === $req->token && Carbon::now() <= $user->TokenExpire) {
        // Invalidate the token and update the user attributes
        $user->Token = null;
        $user->TokenExpire = null;
        $user->LoginAttempt = 0;
        $user->IsBlocked = false;

        $this-> Securities($user->Email);

        // Save the user
        $user->save();

        $s = Security::where('userId', $user->UserId)->orderBy('created_at', 'desc')->first();

        // Prepare the response data
        $responseData = [
            "FullName" => $user->Name,
            "UserId" => $user->UserId,
            "profilePic" => $user->Picture,
            "Role" => $user->Role,
            "SessionId"=>$s->SessionId
        ];

        // Return the response
        return response()->json(["message" => $responseData], 200);
    } else if (Carbon::now() > $user->TokenExpire) {
        return response()->json(["message" => "Your Token Has Expired"], 400);
    } else {
        return response()->json(["message" => "Invalid Token"], 400);
    }
}


function Securities($Email) {

    $user = Partner::where('Email', $Email)->first();


    if ($user == null) {
        return response()->json(["message" => "User does not exist"], 400);
    }

    $ipAddress = $_SERVER['REMOTE_ADDR']; // Get user's IP address

    try{
        $ipDetails = json_decode(file_get_contents("https://ipinfo.io/{$ipAddress}/json"));

    $country = $ipDetails->country ?? 'Unknown';
    $city = $ipDetails->city ?? 'Unknown';
    $latitude = $ipDetails->loc ?? ''; // Latitude

    }catch(\Exception $e){
        $country = $city = $latitude = null;
    }

    // Get user agent information
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Parse the user agent string to determine device and OS
    $device = $this->detectDevice($userAgent);
    $os =  $this->detectOperatingSystem($userAgent);


    $urlPath = $_SERVER['REQUEST_URI'];

    $googleMapsLink = "https://maps.google.com/?q={$latitude}";

    // Create a new AuditTrail instance and save the log to the database
    $auditTrail = new Security();
    $auditTrail->ipAddress = $ipAddress??" ";
    $auditTrail->country = $country??" ";
    $auditTrail->city = $city??" ";
    $auditTrail->device = $device??" ";
    $auditTrail->os = $os??" ";
    $auditTrail->urlPath = $urlPath??" ";
    $auditTrail->action = "Session ID";
    $auditTrail->googlemap = $googleMapsLink??" ";
    $auditTrail -> userId = $user->UserId??" ";
    $auditTrail -> userName = $user->Name;
    $auditTrail -> userPic = $user->Picture??" ";
    $auditTrail->SessionId = $this->TokenGenerator();
     $auditTrail->lastLogin = Carbon::now();




    $auditTrail->save();
}


function detectDevice($userAgent) {
    $isMobile = false;
    $mobileKeywords = ['Android', 'webOS', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone'];

    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            $isMobile = true;
            break;
        }
    }

    return $isMobile ? 'Mobile' : 'Desktop';
}

// Function to detect operating system from User-Agent string
function detectOperatingSystem($userAgent) {
    $os = 'Unknown';

    $osKeywords = ['Windows', 'Linux', 'Macintosh', 'iOS', 'Android'];

    foreach ($osKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            $os = $keyword;
            break;
        }
    }

    return $os;
}



function TokenGenerator(): string {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$^&*()_+{}|<>-=[],.';
        $length = 30;
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
}



    function IdGenerator(): string {
        $randomID = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        return $randomID;
        }







}
