<?php
namespace App\Service;

use App\CafeWallet;

class CafeService {
    /**
     * create cafe wallet
     */
    public function createCafeWallet($cafe,$user) {
        //check if cafe already has a wallet
        $alreadyExist = CafeWallet::where('cafe',$cafe->id)->first();
        if(!$alreadyExist) {
            $cafeWallet = CafeWallet::create([
                'cafe' => $cafe->id,
                'availableBal' => (float)0.00,
                'previousBal' => (float)0.00,
                'virtualMoney' => (float)0.00,
                'user1' => $user->id
            ]);
            if($cafeWallet) {
                return $cafeWallet;
            }else{
                
            }
        }
    }
}