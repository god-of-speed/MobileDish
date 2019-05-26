<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\User;
use App\CafePurchase;
use App\Notification;
use App\CafeCustomRequest;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{


    /**
     * 
     * verify user by email
     */
    public function verifyUser(Request $request) {
        //verify user by mail
        $user = Auth::guard('api')->user();
        if($user) {
            //set content
            $content = [
                "user" => $user,
                "url" => "http://www.mobileDish.com/user/verified?email=".$user->email
            ];
            $mail = Mail::send(new VerifyUserEmail($content));
            if($mail) {
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
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }




    public function verifiedUser(Request $request) {
        //get email
        $email = $request->query('email');
        //get user with email
        $user = User::where('email',$email)->first();
        if($user) {
            //get current datetime
            $now = new DateTime();
            //update user
            $update = $user->update([
                "email_verified"
            ]);
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }






    /**
     * display user
     */
    public function profile(Request $request) {
        //get user 
        $user = Auth::guard('api')->user();
        if($user) {
            return response()->json([
                "user" => $user
            ],Response::HTTP_OK);
        }
        return response()->json([
            "error" => "Unauthorized"
        ],Response::HTTP_UNAUTHORIZED);
    }




    /**
     * display user order
     */
    public function userPendingPurchase(Request $request,IndexService $indexService) {
        //get user
        $user = Auth::guard('api')->user();
        //get page and statrt
        $page = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($user) {
            //get all pending purchases
            $purchases = CafePurchase::where([
                ['user',$user->id],
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
                ['user',$user->id],
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
                "data" => "No order yet"
            ],Response::HTTP_NO_CONTENT);
        }
    }




    /**
     * create custom request
     */
    public function createCustomRequest(Request $request) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            //get location
            $data = $request->only('location','state','country');
            if($data['location'] && $data['state'] && $data['country']) {
                return response()->json(true,Response::HTTP_OK);
            }
            else{
                return response()->json([
                    "error" => "Location was not given, please allow us to locate you"
                ],Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }




    /**
     * store custom request
     */
    public function storeCustomRequest(Request $request) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            $data = $request->only('customRequest','price','duration','location','state','country');
            if($data['country']) {
                if($data['state']) {
                    if($data['location']) {
                        //get cafe by location
                        $cafe = Cafe::where(
                            ['country','LIKE','%{$data["country"]}%'],
                            ['state','LIKE','%{$data["state"]}%'],
                            ['location','LIKE','%{$data["location"]}%']
                        )
                        ->get();
                        //count result
                        $numResult = count($cafe);
                        if($numResult > 1) {
                            $random = floor(rand(0,$numResult));
                            $cafe = $cafe[$random];
                        }
                        if($cafe) {
                            //store customRequest
                            $customRequest = CafeCustomRequest::create([
                                "cafe" => $cafe->id,
                                "user" => $user->id,
                                "customRequest" => $data['customRequest'],
                                "price" => $data['price'],
                                "duration" => $data['duration']
                            ]);
                            if($customRequest) {
                                return response()->json([
                                    "customRequest" => $customRequest,
                                    "user" => $customRequest->user()->first(),
                                    "cafe" => $customRequest->cafe()->first()
                                ],Response::HTTP_CREATED);
                            }
                            else{
                                return response()->json([
                                    "error" => "Internal error"
                                ],Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        }
                        else{
                            //get cafe by state
                            $cafe = Cafe::where(
                                ['country','LIKE','%{$data["country"]}%'],
                                ['state','LIKE','%{$data["state"]}%']
                            )
                            ->get();
                            //count result
                            $numResult = count($cafe);
                            if($numResult > 1) {
                                $random = floor(rand(0,$numResult));
                                $cafe = $cafe[$random];
                            }

                            if($cafe) {
                                //store customRequest
                                $customRequest = CafeCustomRequest::create([
                                    "cafe" => $cafe->id,
                                    "user" => $user->id,
                                    "customRequest" => $data['customRequest'],
                                    "price" => $data['price'],
                                    "duration" => $data['duration']
                                ]);
                                if($customRequest) {
                                    return response()->json([
                                        "customRequest" => $customRequest,
                                        "user" => $customRequest->user()->first(),
                                        "cafe" => $customRequest->cafe()->first()
                                    ],Response::HTTP_CREATED);
                                }
                                else{
                                    return response()->json([
                                        "error" => "Internal error"
                                    ],Response::HTTP_INTERNAL_SERVER_ERROR);
                                }
                            }
                            else{
                                //get cafe by country
                                $cafe = Cafe::where(
                                    ['country','LIKE','%{$data["country"]}%']
                                )
                                ->get();
                                //count result
                                $numResult = count($cafe);
                                if($numResult > 1) {
                                    $random = floor(rand(0,$numResult));
                                    $cafe = $cafe[$random];
                                }
                                if($cafe) {
                                    //store customRequest
                                    $customRequest = CafeCustomRequest::create([
                                        "cafe" => $cafe->id,
                                        "user" => $user->id,
                                        "customRequest" => $data['customRequest'],
                                        "price" => $data['price'],
                                        "duration" => $data['duration']
                                    ]);
                                    if($customRequest) {
                                        return response()->json([
                                            "customRequest" => $customRequest,
                                            "user" => $customRequest->user()->first(),
                                            "cafe" => $customRequest->cafe()->first()
                                        ],Response::HTTP_CREATED);
                                    }
                                    else{
                                        return response()->json([
                                            "error" => "Internal error"
                                        ],Response::HTTP_INTERNAL_SERVER_ERROR);
                                    }
                                }
                                else{
                                    return response()->json([
                                        "error" => "No cafe was found for your location."
                                    ],Response::HTTP_NO_CONTENT);
                                }
                            }
                        }
                    }
                    else{
                        return response()->json([
                            "error" => "Location was not given, please allow us to locate you"
                        ],Response::HTTP_NOT_ACCEPTABLE);
                    }
                }
                else{
                    return response()->json([
                        "error" => "Location was not given, please allow us to locate you"
                    ],Response::HTTP_NOT_ACCEPTABLE);
            }
            }
            else{
                return response()->json([
                    "error" => "Location was not given, please allow us to locate you"
                ],Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }




    /**
     * cancel join request
     */
    public function cancelJoinRequest(Request $request) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            //get request
            $joinRequest = $request->query('request');
            $joinRequest = $joinRequest == null  && !is_int($joinRequest) ? false : Cafe_Member::where(
                ['id',$joinRequest],
                ['user',$user->id]
            )
            ->first();
            //get notifications and delete
            $notifications = Notification::where(
                ['type','joinRequest'],
                ['id',$joinRequest->id]
            )
            ->get();
            foreach($notifications as $notify) {
                $notify->delete();
            }
            //delete join request
            $joinRequest->delete();
            return response()->json(true,Response::HTTP_OK);
        }
        else{
            return response()->json([
                'error' => 'Unauthorized'
            ],Response::HTTP_UNAUTHORIZED); 
        }
    }




    /**
     * get user notification
     */
    public function userNotification(Request $request,IndexService $indexService) {
        //get user
        $user = Auth::guard('api')->user();
        if($user) {
            //get page and start
            $page = $request->query('p');
            $start = $request->query('s');
            $display = 30;
            //get user latest notification
            $notifications = Notification::where([
                ['user',$user->id],
                ['status',false]
            ])
            ->orderBy('created_at','desc')
            ->get();
            if($notifications) {
                //paginate result
                $arr = $indexService->pagination($page,$start,$display,$notifications);
                $p = $arr['p'];
                $s = $arr['s'];
                $result = Notification::where([
                    ['user',$user->id],
                    ['status',false]
                ])
                ->orderBy('created_at','desc')
                ->take($display)
                ->skip($s)
                ->get();
                return response()->json([
                    "result" => $result,
                    "p" => $p,
                    "s" => $s
                ],Response::HTTP_OK);
            }
            else{
                return response()->json("No notifications yet",Response::HTTP_NO_CONTENT);
            }
        }
        else{
            return response()->json([
                "error" => "Unauthorized"
            ],Response::HTTP_UNAUTHORIZED);
        }
    }




    /**
     * user purchase
     */
    public function createUserPurchase(Request $request) {
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
    public function userSingleStorePurchase(Request $request,NotificationService $notify) {
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
     * change purchase status
     */
    public function changeUserPurchaseStatus(Request $request) {
        //get purchase
        $purchase = $request->query('purchase');
        $purchase = $purchase == null && !is_int($purchase) ? false : CafePurchase::find($purchase);
        if($purchase) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //get status
                $status = $request->query('status');
                if($status == "end") {
                    //update purchase
                    $update = $purchase->update(
                        ['status','end']
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
                    //update purchase
                    $update = $purchase->update(
                        ['status','cancel']
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
