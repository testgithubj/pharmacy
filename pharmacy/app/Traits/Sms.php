<?php
namespace App\Traits;


use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;

trait Sms
{
    function sendSms($contact,$msg) {
        $url = "http://sms.viatech.com.bd:8809/smsapi";
        $data = [
            "api_key" => env('METROBD_API_KEY'),
            "type" => "text",
            "contacts" => $contact,
            "senderid" => env('METROBD_SENDER_ID'),
            "msg" => $msg,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}




