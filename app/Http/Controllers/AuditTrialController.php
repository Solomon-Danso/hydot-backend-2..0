<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\SessionMgmt;
use App\Models\AuditTrial;
use App\Models\Visitors;


class AuditTrialController extends Controller
{
    function Auditor($UserId, $Action) {
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

        // Current date and time


        // URL path
        $urlPath = $_SERVER['REQUEST_URI'];

        $stu = AdminUser::where('UserId', $UserId)->first();
        if($stu==null){
            return response()->json(["message"=>"Admin does not exist"],400);
        }



        $googleMapsLink = "https://maps.google.com/?q={$latitude}";

        // Create a new AuditTrail instance and save the log to the database
        $auditTrail = new AuditTrial();
        $auditTrail->ipAddress = $ipAddress??" ";
        $auditTrail->country = $country??" ";
        $auditTrail->city = $city??" ";
        $auditTrail->device = $device??" ";
        $auditTrail->os = $os??" ";
        $auditTrail->urlPath = $urlPath??" ";
        $auditTrail->action = $Action??" ";
        $auditTrail->googlemap = $googleMapsLink??" ";
        $auditTrail -> userId = $stu->UserId??" ";
        $auditTrail -> userName = $stu->Name;
        $auditTrail -> userPic = $stu->Picture??" ";
        $auditTrail -> companyId = $stu->UserId??" ";


        $auditTrail->save();
    }

    function Visitors() {
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

        // Current date and time


        // URL path
        $urlPath = $_SERVER['REQUEST_URI'];

        $googleMapsLink = "https://maps.google.com/?q={$latitude}";

        // Create a new AuditTrail instance and save the log to the database
        $auditTrail = new Visitors();
        $auditTrail->IpAddress = $ipAddress??" ";
        $auditTrail->Country = $country??" ";
        $auditTrail->City = $city??" ";
        $auditTrail->Device = $device??" ";
        $auditTrail->Os = $os??" ";
        $auditTrail->googlemap = $googleMapsLink??" ";

        $auditTrail->save();
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


    function IdGenerator(): string {
        $randomID = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        return $randomID;
    }



}
