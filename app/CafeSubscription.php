<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeSubscription extends Model
{
    /**
     * set fillable
     * 
     */
    protected $fillable = [
        "cafe","suscriber"
    ];

    /**
     * get cafe
     * 
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }

    /**
     * get user
     */
    public function user() {
        return $this->belongsTo('App\User','user');
    }
}
