<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * set fillables
     */
    protected $fillable = [
        "user","type","extId","comment","url","status"
    ];

    /**
     * get user
     */
    public function user() {
        return $this->belongsTo('App\User','user');
    }
}
