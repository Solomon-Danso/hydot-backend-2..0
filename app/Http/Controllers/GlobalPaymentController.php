<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\GlobalPayment;
use Illuminate\Support\Facades\Http;


class GlobalPaymentController extends Controller
{

public function AddPayment(Request $req){

$s = new GlobalPayment();


$options = ["tref", "ProductId", "Product", "Username", "Amount", "SuccessApi", "CallbackURL"];

foreach ($options as $option) {

    if($req->filled($option)){
        $s->$option = $req->$option;
    }
}

$s->IsExecuted = false;
$s->save();

}




public function GetPayment(Request $req){
    $s = GlobalPayment::where("tref", $req->tref)->first();

    if ($s == null) {
        return response()->json(["message" => "Payment Not Found"], 400);
    }

    if ($s->IsExecuted == true) {
        return response()->json(["message" => "Payment already recorded"], 400);
    }

    $successApiUrl = $s->SuccessApi;

    try {
        $response = Http::timeout(60)->get($successApiUrl); // Increase timeout to 60 seconds
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        return response()->json(["message" => "Failed to execute Success API: " . $e->getMessage()], 500);
    }

    if ($response->failed()) {
        return response()->json(["message" => "Failed to execute Success API"], 400);
    }

    $s->IsExecuted = true;
    $s->save();

    return response()->json(["message" => $s->CallbackURL], 200);
}


public function TestPayment(){
    return "Payment Api is working";
}




}
