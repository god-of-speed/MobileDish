<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeCategory extends Model
{
    /**
     * set fillables
     * 
     */
    protected $fillable = [
        "cafe","menu","name","about"
    ];

    /**
     * get cafe
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }

    /**
     * get menu
     */
    public function menu() {
        return $this->belongsTo('App\Cafe_Menu','menu');
    }
}
