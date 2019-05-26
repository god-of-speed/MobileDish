<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeCustomRequest extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "cafe","user","customRequest","price","duration",
        "discount","userStatus","cafeStatus"
    ];

    /**
     * get cafe
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
