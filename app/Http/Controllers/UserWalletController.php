<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserWalletController extends Controller
{
    /**
     * get user wallet info
     */
    public function userWallet(Request $request) {
        //get user wallet info
        $user = Auth::guard('api')->user();
        if($user) {
            $userWallet = UserWallet::where('user',$user->id)->get();
            if($userWallet) {
                return response()->json([
                    "userWallet" => $userWallet,
                    "user" => $user
                ],Response::HTTP_OK);
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
}
