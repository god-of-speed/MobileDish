<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeItem;
use App\Cafe_Member;
use App\CafePurchase;
use Illuminate\Http\Request;
use App\Service\IndexService;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class CafePurchaseController extends Controller
{
    /**
     * cafe purchase
     */
    public function Create(Request $request) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            return response()->json(true,Response::HTTP_OK);
        }
        else{
            return response()->json(false,Response::HTTP_UNAUTHORIZED);
        }
    }




    /**
     * store purchase
     */
    public function singleStore(Request $request,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get item
            $item = $request->query('item');
            $item = $item == null && !is_int($item) ? false : CafeItem::find($item);
            if($item) {
                //get user
                $user = Auth::guard('api')->user();
                if($user) {
                    //get data
                    $data = $request->only('location','state','country');
                    //save purchase
                    $purchase = CafePurchase::create([
                        "cafe" => $cafe->id,
                        "item" => $item->id,
                        "user" => $user->id,
                        "quantity" => 1,
                        "location" => $data['location'],
                        "country" => $data['country'],
                        "state" => $data['state']
                    ]);
                    if($purchase) {
                        //get cafe members
                        $members = Cafe_Member::where(
                            ['cafe',$cafe->id],
                            ['status',"confirmed"]
                        )
                        ->get();
                        foreach($members as $member) {
                            $notify->createNotification($member->user()->first()->id,"purchase",$purchase->id,$purchase->user()->first()->username." purchased ".$purchase->quantity." of ".$purchase->item()->first()->name,"/cafe/purchase?cafe=".$purchase->cafe()->first()->id);
                        }
                        return response()->json(true,Response::HTTP_CREATED);
                    }
                    else{
                        return response()->json([
                            "error" => "Internal server error"
                        ],Response::HTTP_INTERNAL_SERVER_ERROR);
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
        else{
            return response()->json([
                "error" => "Resource not found"
            ],Response::HTTP_BAD_REQUEST);
        }
    }




    /**
     * list pending purchases
     */
    public function pendingPurchase(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        //get page and statrt
        $page = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($cafe) {
            //get all pending purchases
            $purchases = CafePurchase::where(
                ['cafe',$cafe->id],
                ['userStatus','!=','end'],
                ['userStatus',"!=","cancel"],
                ['cafeStatus','!=','end'],
                ['cafeStatus',"!=","cancel"]
            )
            ->orderBy('created_at','asc')
            ->get();
            //get page and start
            $arr = $indexService->pagination($page,$start,$display,$purchases);
            $p = $arr['p'];
            $s = $arr['s'];
            $result = CafePurchase::where(
                ['cafe',$cafe->id],
                ['status','!=','end']
            )
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
}
