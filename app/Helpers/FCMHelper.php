<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

class FCMHelper
{
    public static function send_notification($message, $to)
    {
        $res['success'] = false;
        try {
            $url = env('FCM_URL');
            $serverKey = env('FCM_SERVER_TOKEN');
        
            $msg =[
                'body'  => $message['body'],
                'title' => $message['title'],
                // 'receiver' => 'erw',
                // 'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
            ];
        
            $fields = [
                'to' => $to,
                'notification'  => [
                    'body'  => $message['body'],
                    'title' => $message['title']
                ]
            ];
        
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
            if (json_decode($result)->failure = 1) {
                throw new Exception(json_decode($result)->results[0]->error, 1);
            }
        } catch (Exception $th) {
            Log::error('Send Notificacion FCM', ['result' => (array)json_decode($result), 'msg' => $message, 'to' => $to]);
            $res['error'] = 'error enviando notificacion';
        }
          
    }
}
