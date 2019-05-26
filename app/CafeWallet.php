<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeWallet extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "cafe","availableBal","previousBal","virtualMoney","user1","user2"
    ];

    protected $dates = ['deleted_at'];

    /**
     * get cafe
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }

    /**
     * get user1
     */
    public function user1() {
        return $this->belongsTo('App\User','user1');
    }

    /**
     * get user2
     */
    public function user2() {
        return $this->belongsTo('App\User','user2');
    }
}
