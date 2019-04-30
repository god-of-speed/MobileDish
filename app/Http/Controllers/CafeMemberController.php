<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\Cafe_Member;
use Illuminate\Http\Request;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Auth;

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
            if(Cafe_Member::where(
                ['user',$user->id],
                ['cafe',$cafe->id],
                ['status','!=','declined']
            )->first()) {
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
                $alreadyExist = Cafe_Member::where(
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['status','!=','declined']
                )->first();
                if(!$alreadyExist) {
                    //create cafe member
                    $cafeMember = Cafe_Member::create([
                        'cafe' => $cafe->id,
                        'user' => $user->id,
                        'right' => 'member',
                        'requestType' => 'join'
                    ]);
                    if($cafeMember) {
                        //notify user
                        $notifyUser = $notify->createNotification($user->id,'Employment Request sent to'.$cafe->name,'user/request');
                        //get all the admin of the cafe
                        $admins = Cafe_Member::where(
                            ['cafe',$cafe->id],
                            ['right','admin']
                        )->get();
                        foreach($admins as $admin) {
                            $notifyAdmin = $notify->createNotification($admin->user()->first()->id,$user->username.' sent an employment request.','/cafe/request?cafe='.$cafe->id);
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
     * confirmRequest 
     */
    public function confirmRequest(Request $request,NotificationService $notify) {
        //get request
        $membershipRequest = $request->query('joinRequest');
        //get member
        $cafeMember = Cafe_Member::find($membershipRequest);
        //update member's status
        if($cafeMember) {
            $update = $cafeMember->update(['status'=>'confirmed']);
            //notify the requester
            $notify->createNotification($update->user()->first()->id,'Congratulations! you have successfully joined '.$update->cafe()->first()->name,'/cafe/request?user='.$update->user()->first()->id);
            return response()->json([
                'details' => [
                    'user' => $cafeMember->user()->first(),
                    'cafe' => $cafeMember->cafe()->first(),
                    'other' => $cafeMember
                ] 
            ],Response::HTTP_CREATED);
        }
        return response()->json([
            'error' => 'Resource not found.'
        ],Response::HTTP_BAD_REQUEST);
    }

    /**
     * rejectRequest
     */
    public function rejectRequest(Request $request,NotificationService $notify) {
        //get request
        $membershipRequest = $request->query('request');
        //get member
        $cafeMember = Cafe_Member::find($membershipRequest);
        //update member's status
        if($cafeMember) {
            $update = $cafeMember->update(['status' => 'rejected']);
            //notify the requester
            $notify->createNotification($update->user()->first()->id,'Sorry! your request was declined','/cafe/request?user='.$update->user()->first()->id);
            return response()->json([
                'details' => [
                    'user' => $cafeMember->user()->first(),
                    'cafe' => $cafeMember->cafe()->first(),
                    'other' => $cafeMember
                ] 
            ],Response::HTTP_CREATED);
        }
        return response()->json([
            'error' => 'Resource not found.'
        ],Response::HTTP_BAD_REQUEST);
    }

    
    /**
     * make admin
     */
    public function makeAdmin(Request $request,NotificationService $notify) {
        //get user
        $user = Auth::guard('api')->user();
        //get cafe
        $cafe = $request->query('cafe');
        //get cafe
        $cafe = Cafe::find($cafe);
        if($cafe) {
            //get cafe members that are admin
            $cafeMembers = Cafe_Member::where(
                ['cafe',$cafe],
                ['status','confirmed'],
                ['right','admin']
            )->get();
            if(count($cafeMembers) < 3) {
                //get cafe member where user is a member
                $member = Cafe_Member::where(
                    ['user',$user->id],
                    ['status','confirmed'],
                    ['right','member']
                )->get();
                if($member) {
                    $updateMember = $member->update(['right' => 'admin']);
                    if($updateMember) {
                        return response()->json([
                            'member' => $member
                        ],Response::HTTP_CREATED);
                    }
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
            $admin = Cafe_Member::where(
                ['user',$user->id],
                ['cafe',$cafe],
                ['status','confirmed'],
                ['right','admin']
            )->first();
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
}
