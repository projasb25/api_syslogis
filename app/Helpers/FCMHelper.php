<?php

namespace App\Helpers;

class FCMHelper
{
    public static function send_notification($message, $to)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $to = 'e0_n4FaWSgmVX83z1caKoM:APA91bEdgdmcY1s2zN1d4gBm0Z6XTr2gwgbSKx7a8sDtrXNUJxg63cWIwSyk82MzReGhEgaUPNdDMF41V4N-uBKc_nI19yEYNffJJH6G2V2RkwANKpONjz-vcCWlOLkgMlJAeLKhm8ao';
            
        $serverKey = 'AAAAs_RyLH8:APA91bEMO84vPt0_SefAJ0SBI1vROjsyIwvbm3Gwq9nd-zYoirTonGZSUH7pRKM2hN9hDqGgmccUKjpK_arHe2tkOkxaqd4j9w3A73Qklr9xK2bU_wasCMbTto7XUTuX74UUbkUzly2b';
    
        $data = [
            "registration_ids" => $to,
            "notification" => [
                "title" => $message['title'],
                "body" => $message['body'],  
            ]
        ];
        
    
        $msg = array
            (
            'body'  => $message['body'],
            'title' => $message['title'],
            'receiver' => 'erw',
            'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
            );
    
        $fields = array
        (
            'to'        => $to,
            'notification'  => $msg
        );
    
        $encodedData = json_encode($fields);
    
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
    
        // Execute post
        $result = curl_exec($ch);
    
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        
    
        // Close connection
        curl_close($ch);
    
        // FCM response
        dd($result);      
    }
}
