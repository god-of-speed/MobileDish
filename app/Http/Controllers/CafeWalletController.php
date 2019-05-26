<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeMember;
use App\CafeWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CafeWalletController extends Controller
{
    /**
     * get cafe wallet info
     */
    public function cafeWallet(Request $request) {
        //get cafe wallet info
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //get cafe admins
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['user',$user->id],
                    ['right','admin'],
                    ['status','confirmed']
                ])
                ->first();
                if($admin) {
                    $cafeWallet = CafeWallet::where([
                        ['cafe',$cafe->id]
                    ])
                    ->get();
                    if($cafeWallet) {
                        return response()->json([
                            "cafeWallet" => $cafeWallet,
                            "cafe" => $cafe
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
                "error" => "Bad request"
            ],Response::HTTP_BAD_REQUEST);
        }
    }
}
