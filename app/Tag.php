<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * fillables
     */
    protected $fillable = ["tagName"];

    /**
     * enable soft delete
     */
    protected $dates = ['deleted_at'];
}
