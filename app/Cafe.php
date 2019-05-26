<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cafe extends Model
{
    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected $fillable = [
        "name","email","picture","about","country","state","location","currency","like"
    ];

    /**
     * enable soft delete
     */
    protected $dates = ['deleted_at'];
}
