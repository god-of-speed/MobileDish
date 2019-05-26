<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeTag extends Model
{
    /**
     * allow mass assignment
     * 
     */
    protected $fillable = ["cafe","tag"];

    /**
     * get the cafe that owns this id
     * 
     */
    public function cafe() {
        return $this->belongsTo('App\Cafe','cafe');
    }

    /**
     * get the tag that owns this id
     * 
     */
    public function tag() {
        return $this->belongsTo('App\Tag','tag');
    }
}
