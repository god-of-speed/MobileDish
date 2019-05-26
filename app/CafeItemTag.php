<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CafeItemTag extends Model
{
    /**
     * allow mass assignment
     * 
     */
    protected $fillable = ['item','tag'];
    /**
     * get item
     */
    public function item() {
        return $this->belongsTo('App\CafeItem','item');
    }

    /**
     * get tag
     */
    public function tag() {
        return $this->belongsTo('App\Tag','tag');
    }
}
