<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafePurchase extends Model
{
    /**
     * set fillables
     * 
     */
    protected $fillable = [
        "cafe","item","user","userStatus","cafeStatus",
        "quantity","comment","country","state","location"
    ];

    /**
     * enable soft delete
     */
    protected $dates = ['deleted_at'];

    /**
     * get cafe
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }

    /**
     * get item
     */
    public function item() {
        return $this->belongsTo('App\CafeItem','item');
    }

    /**
     * get user
     */
    public function user() {
        return $this->belongsTo('App\User','user');
    }
}
