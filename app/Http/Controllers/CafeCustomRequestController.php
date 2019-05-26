<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeMember;
use App\CafeCustomRequest;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CafeCustomRequestController extends Controller
{
    /**
     * list cafe custom Request
     */
    public function cafeCustomRequest(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get page and start
            $page = $request->query('p');
            $start = $request->query('s');
            $display = 30;
            //get pending customRequests
            $customRequests = CafeCustomRequest::where([
                ['cafe',$cafe->id],
                ['userStatus','!=','end'],
                ['userStatus',"!=","cancel"],
                ['cafeStatus','!=','end'],
                ['cafeStatus',"!=","cancel"]
            ])
            ->orderBy('created_at','asc')
            ->get();
            $arr = $indexService->pagination($page,$start,$display,$customRequests);
            $p = $arr['p'];
            $s = $arr['s'];
            $result = CafeCustomRequest::where([
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
     * end customRequest
     */
    public function endCafeCustomRequest(Request $request) {
        //get cafe 
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //get admins
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['right','admin'],
                    ['status','confirmed']
                ])
                ->get();
                if($admin) {
                    //get customRequest
                    $customRequest = $request->query('customRequest');
                    $customRequest = $customRequest == null && !is_int($customRequest) ? false : CafeCustomRequest::find($customRequest);
                    if($customRequest) {
                        $update = $customRequest->update([
                            "cafeStatus" => "end"
                        ]);
                        if($update) {
                            return response()->json(true,Response::HTTP_OK);
                        }
                        else{
                            return response()->json([
                                "error" => "INternal server error"
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




    /**
     * cancel customRequest
     */
    public function cancelCafeCustomRequest(Request $request) {
        //get cafe 
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //get admins
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['right','admin'],
                    ['status','confirmed']
                ])
                ->get();
                if($admin) {
                    //get customRequest
                    $customRequest = $request->query('customRequest');
                    $customRequest = $customRequest == null && !is_int($customRequest) ? false : CafeCustomRequest::find($customRequest);
                    if($customRequest) {
                        $update = $customRequest->update([
                            "cafeStatus" => "cancel"
                        ]);
                        if($update) {
                            return response()->json(true,Response::HTTP_OK);
                        }
                        else{
                            return response()->json([
                                "error" => "INternal server error"
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
