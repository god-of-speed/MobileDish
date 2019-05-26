<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "user","availableBal","previousBal","virtualBal"
    ];

    /**
     * enable soft delete
     */
    protected $dates = ['deleted_at'];

    /**
     * get user
     */
    public function user() {
        return $this->belongsTo('App\User','user');
    }
}
