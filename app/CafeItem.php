<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeItem extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "cafe","menu","category","name","price","oldPrice","discount","about","status"
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

    /**
     * get category
     */
    public function category() {
        return $this->belongsTo('App\Cafe_Category','category');
    }
}
