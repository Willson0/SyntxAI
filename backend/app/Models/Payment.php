<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = "payments";
    protected $guarded = false;
    public $timestamps = false;

}
