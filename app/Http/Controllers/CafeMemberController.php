<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\User;
use App\CafeMember;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class CafeMemberController extends Controller
{
    /**
     * check if user is a member
     */
    public function checkIfUserIsAMember(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if(CafeMember::where([
                ['user',$user->id],
                ['cafe',$cafe->id],
                ['status','!=','declined']
            ])->first()) {
                return response()->json(true,Response::HTTP_OK);
            }else{
                return response()->json(false,Response::HTTP_OK);
            }
        }
        else{
            return response()->json([
                "error" => "Resource not found"
            ],Response::HTTP_BAD_REQUEST);
        }
    }





    /**
     * join cafe
     */
    public function joinCafe(Request $request,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //check if user has requested or is in a cafe
                $alreadyExist = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['status','!=','declined']
                ])->first();
                if(!$alreadyExist) {
                    //create cafe member
                    $cafeMember = CafeMember::create([
                        'cafe' => $cafe->id,
                        'user' => $user->id,
                        'right' => 'member',
                        'requestType' => 'join'
                    ]);
                    if($cafeMember) {
                        //notify user
                        $notifyUser = $notify->createNotification($user->id,'Employment Request sent to'.$cafe->name,'user/request');
                        //get all the admin of the cafe
                        $admins = CafeMember::where([
                            ['cafe',$cafe->id],
                            ['right','admin']
                        ])->get();
                        foreach($admins as $admin) {
                            $notifyAdmin = $notify->createNotification($admin->user()->first()->id,"cafeMember",$cafeMember->id,$user->username.' sent an employment request.','/cafe/request?cafe='.$cafe->id);
                        }
                        return response()->json(true,Response::HTTP_CREATED);
                    }else{
                        return response()->json([
                            'error' => 'Internal server error.'
                        ],Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }else{
                    return response()->json(true,Response::HTTP_OK);
                }
            }else{
                return response()->json([
                    "error" => "Unauthorized"
                ],Response::HTTP_UNAUTHORIZED);
            }
        }else{
            return response()->json([
                'error' => 'Resource not found.'
            ],Response::HTTP_BAD_REQUEST);
        }
    }



    
    /**
     * make admin
     */
    public function makeAdmin(Request $request,NotificationService $notify) {
        //get user
        $user = Auth::guard('api')->user();
        //get admin 
        $admin = $request->query('user');
        $admin = $admin == null && !is_int($admin) ? false : User::find($admin);
        //get cafe
        $cafe = $request->query('cafe');
        //get cafe
        $cafe = Cafe::find($cafe);
        if($cafe) {
            //get cafe members that are admin
            $cafeMembers = CafeMember::where([
                ['cafe',$cafe],
                ['status','confirmed'],
                ['right','admin']
            ])->get();
            if(count($cafeMembers) == 0) {
                //get cafe member where individual is a member
                $member = CafeMember::where([
                    ['user',$user->id],
                    ['status','confirmed'],
                    ['right','member']
                ])->first();
                if($member) {
                    $updateMember = $member->update(['right' => 'admin']);
                    if($updateMember) {
                        return response()->json([
                            'member' => $member
                        ],Response::HTTP_CREATED);
                    }
                }
                return response()->json([
                    'error' => 'Error.'
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            else{
                if(CafeMember::where([
                    ['user',$user->id],
                    ['status','confirmed'],
                    ['right','admin']
                ])->first()) {
                    if($admin) {
                        //get cafe member where individual is a member
                        $member = CafeMember::where([
                            ['user',$admin->id],
                            ['status','confirmed'],
                            ['right','member']
                        ])->first();
                        if($member) {
                            $updateMember = $member->update(['right' => 'admin']);
                            if($updateMember) {
                                return response()->json([
                                    'member' => $member
                                ],Response::HTTP_CREATED);
                            }
                        }
                        return response()->json([
                            'error' => 'Error.'
                        ],Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    else{
                        return response()->json([
                            "error" => "User doesn't exist"
                        ],Response::HTTP_BAD_REQUEST);
                    }
                }
                return response()->json([
                    "error" => "No right"
                ],Response::HTTP_FORBIDDEN);
            }
        }
        return response()->json([
            'error' => 'Resource not found.'
        ],Response::HTTP_BAD_REQUEST);
    }

    /**
     * remove admin
     */
    public function removeAdmin(Request $request,NotificationService $notify) {
        //get user
        $user = Auth::guard('api')->user();
        //get cafe
        $cafe = $request->query('cafe');
        //get cafe
        $cafe = Cafe::find($cafe);
        if($cafe) {
            //check if user is actually an admin
            $admin = CafeMember::where([
                ['user',$user->id],
                ['cafe',$cafe->id],
                ['status','confirmed'],
                ['right','admin']
            ])->first();
            if($admin) {
                $updateMember = $admin->update(['right'=>'member']);
                if($updateMember) {
                    return response()->json([
                        'member' => $admin
                    ],Response::HTTP_CREATED);
                }
            }
            return response()->json([
                'error' => 'Error.'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            'error' => 'Resource not found.'
        ],Response::HTTP_BAD_REQUEST);
    }




    /**
     * ge3t all cafe members
     * 
     */
    public function CafeMembers(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //validate user
            if(CafeMember::where([
                ['cafe',$cafe->id],
                ['user',Auth::guard('api')->id()],
                ['status','confirmed']
            ])
            ->first()
            ) {
                //get cafe members
                $members = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['status','confirmed']
                ])
                ->get();
                if($members) {
                    //get page and start
                    $page = $request->query('p');
                    $start = $request->query('s');
                    $display = 30;
                    $arr = $indexService->pagination($page,$start,$display,$members);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    if($arr) {
                        //paginate result
                        $result = CafeMember::where([
                            ['cafe',$cafe->id],
                            ['status','confirmed']
                        ])
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
                        return response()->json([
                            "error" => "Not Found!"
                        ],Response::HTTP_BAD_REQUEST);
                    }
                }
            else{
                return response()->json([
                    "error" => "No member yet"
                ],Response::HTTP_NO_CONTENT);
            }
            }   
        }
        return response()->json([
            "error" => "Resource not found"
        ],Response::HTTP_BAD_REQUEST);
    }
}
