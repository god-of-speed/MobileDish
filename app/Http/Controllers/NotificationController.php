<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
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




    /**
     * get all notification
     */
    public function notifications(Request $request, IndexService $indexService) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            //get page and start
            $page = $request->query('p');
            $start = $request->query('s');
            $display = 40;
            //get all user notications
            $notifications = Notification::where(
                ['user',$user->id],
                ['status',false]
            )
            ->get();
            if($notifications) {
                //get paginated result
                $arr = $indexService->pagination($page,$start,$display,$notifications);
                $p = $arr['p'];
                $s = $arr['s'];
                //get notifications
                $notifications = Notification::where(
                    ['user',$user->id],
                    ['status',false]
                )
                ->take($display)
                ->skip($s)
                ->get();

                return response()->json([
                    "notifications" => $notifications,
                    "p" => $p,
                    "s" => $s
                ],Response::HTTP_OK);
            }
            else{
                return response()->json("No content",Response::HTTP_NO_CONTENT);
            }
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }
}
