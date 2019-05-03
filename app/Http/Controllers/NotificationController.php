<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    /**
     * mark notification as seen
     *
     */
    public function seenNotification(Request $request) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            //get notification
            $notification = $request->query('notification');
            $notification = $notification == null && !is_int($notification) ? false : Notification::find($notification);
            if($notification) {
                //update notification
                $update = $notification->update([
                    "status" => true
                ]);
                if($update) {
                    return response()->json(true,Response::HTTP_OK);
                }
                else{
                    return response()->json([
                        "error" => "Internal server error"
                    ],Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            else{
                return response()->json([
                    "error" => "Resource not found"
                ],Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }
}
