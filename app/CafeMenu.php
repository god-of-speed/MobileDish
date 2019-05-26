<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeMenu extends Model
{
    /**
     * set fillables
     * 
     */
    protected $fillable = [
        "cafe","name","about"
    ];

    /**
     * get cafe
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }
}
