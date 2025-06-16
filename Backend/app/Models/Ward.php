<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    

     public $timestamps = false;

    protected $fillable = ['code', 'name', 'district_code'];
}
