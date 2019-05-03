<?php
namespace App\Service;

use App\Notification;

class NotificationService {
    //create notification
    public function createNotification($user,$type,$extId,$comment,$url) {
        $notification = Notification::create([
            'user' => $user,
            "type" => $type,
            "extId" => $extId,
            'comment' => $comment,
            'url' => $url
        ]);
        if($notification) {
            return $notification;
        }else{
            
        }
    }
}