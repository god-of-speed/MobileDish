<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeItem;
use App\CafeMember;
use App\CafePurchase;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CafePurchaseController extends Controller
{
    /**
     * list pending purchases
     */
    public function cafePendingPurchase(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        //get page and statrt
        $page = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($cafe) {
            //get all pending purchases
            $purchases = CafePurchase::where([
                ['cafe',$cafe->id],
                ['userStatus','!=','end'],
                ['userStatus',"!=","cancel"],
                ['cafeStatus','!=','end'],
                ['cafeStatus',"!=","cancel"]
            ])
            ->orderBy('created_at','asc')
            ->get();
            //get page and start
            $arr = $indexService->pagination($page,$start,$display,$purchases);
            $p = $arr['p'];
            $s = $arr['s'];
            $result = CafePurchase::where([
                ['cafe',$cafe->id],
                ['userStatus','!=','end'],
                ['userStatus',"!=","cancel"],
                ['cafeStatus','!=','end'],
                ['cafeStatus',"!=","cancel"]
            ])
            ->orderBy('created_at','asc')
            ->take($display)
            ->skip($s)
            ->get();
            if($result) {
                return response()->json([
                    "result" => $result,
                    "s" => $s,
                    "p" => $p
                ],Response::HTTP_OK);
            }
            else{
                return response()->json([
                    "error" => "Bad request"
                ],Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return response()->json([
                "error" => "Resource not found"
            ],Response::HTTP_BAD_REQUEST);
        }
    }




    /**
     * change purchase status
     */
    public function changeCafePurchaseStatus(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user 
            $user = Auth::guard('api')->user();
            if($user) {
                //check if user is admin
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['right','admin'],
                    ['status','confirmed']
                ])
                ->first();
                if($admin) {
                    //get purchase
                    $purchase = $request->query('purchase');
                    $purchase = $purchase == null && !is_int($purchase) ? false : CafePurchase::find($purchase);
                    if($purchase) {
                        //get status
                        $status = $request->query('status');
                        if($status == "end") {
                            $update = CafePurchase::update(
                                ['cafeStatus','end']
                            );
                            if($update) {
                                return response()->json(true,Response::HTTP_OK);
                            } 
                            else{
                                return response()->json([
                                    "error" => "Internal server error"
                                ],Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        }
                        elseif($status == "cancel") {
                            $update = CafePurchase::update(
                                ['cafeStatus','cancel']
                            );
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
                                "error" => "Bad request"
                            ],Response::HTTP_BAD_REQUEST);
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
                        "error" => "Forbidden"
                    ],Response::HTTP_FORBIDDEN);
                }
            }
            else{
                return response()->json([
                    "error" => "Unauthorized"
                ],Response::HTTP_UNAUTHORIZED);
            }
        }
        else{
            return response()->json([
                "error" => "Resource not found"
            ],Response::HTTP_BAD_REQUEST);
        }
    }
}
