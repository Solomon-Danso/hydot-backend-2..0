<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Mail;
use App\Mail\Authentication;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Security;


class AuthenticationController extends Controller
{



    public function LogIn(Request $req)
    {
    
        // Use your custom Authentication model to authenticate
        $user = AdminUser::where('Email', $req->Email)->first();
    
        if ($user && Hash::check($req->Password, $user->Password)) {
            
            if($user->IsBlocked==true){
                return response()->json(['message' => 'You have exceeded your Login Attempts'], 500);
            }
            else{
                
                $user->Token = $this->IdGenerator();
                $user->TokenExpire = Carbon::now()->addMinutes(10);

                $saver = $user->save();
                if ($saver) {
                    // Send email if the request is successful
                    try {
                        Mail::to("solomondanso2023@gmail.com")->send(new Authentication( $user->Token));
                        return response()->json(['message' => $user->UserId], 200);
                    } catch (\Exception $e) {
                      
                        return response()->json(['message' => 'Email Request Failed'], 400);
                    }
                    
           
                   
                } else {
                    return response()->json(['message' => 'Could not save the Token'], 500);
                }


            }

           
        } else {
            $user->LoginAttempt += 1;

            if($user->LoginAttempt>2){
                $user->IsBlocked=true;

            }
            $user->save();

            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

function Unlocker(Request $req){
    $user = AdminUser::where('Email', $req->email)->first(); 
    if($user==null){
        return response()->json(["message"=>"User does not exist"],400);
    }
    $user->Token = null;
    $user->TokenExpire = null;
    $user->LoginAttempt = 0;
    $user -> IsBlocked = false;

    $saver=  $user -> save();

 if ($saver) {
            return response()->json(["Result" => "Success"], 200);
        } else {
            return response()->json(["Result" => "Failed"], 500);
        }


}



function VerifyToken(Request $req){
    $user = AdminUser::where('Email', $req->Email)->first();

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

        // Prepare the response data
        $responseData = [
            "FullName" => $user->Name,
            "UserId" => $user->UserId,
            "profilePic" => $user->Picture,
            "Role" => $user->Role,
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

    $user = AdminUser::where('Email', $Email)->first();


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
    $auditTrail->last_activity = Carbon::now()->timestamp;
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
