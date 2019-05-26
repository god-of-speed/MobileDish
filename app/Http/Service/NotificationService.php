<?php
namespace App\Http\Service;

use App\Notification;

class NotificationService {
    //create notification
    public function createNotification($user,$type = null,$extId = null,$comment,$url) {
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