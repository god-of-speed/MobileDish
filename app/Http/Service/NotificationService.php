<?php
namespace App\Service;

use App\Notification;

class NotificationService {
    //create notification
    public function createNotification($user,$comment,$url) {
        $notification = Notification::create([
            'user' => $user,
            'comment' => $comment,
            'url' => $url
        ]);
        if($notification) {
            return $notification;
        }else{
            
        }
    }
}