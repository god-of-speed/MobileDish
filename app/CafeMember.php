<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeMember extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "cafe","user","right","status","requestType"
    ];

    /**
     * enable softDelete
     */
    protected $dates = ['deleted_at'];

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
