<?php
namespace App\Http\Service;

use App\UserWallet;

class UserService {
    //create wallet for user
    public function createWallet($user) {
        //check if the user already has a wallet
        $alreadyExist = UserWallet::where('user',$user->id)->first();
        if(!$alreadyExist) {
            $wallet = UserWallet::create([
                'user' => $user->id,
                'availableBal' => (float)0.00,
                'previousBal' => (float)0.00,
                'virtualBal' => (float)0.00
            ]);
            //check
            if($wallet) {
                return $wallet;
            }else{
                
            }
        }
    }
}